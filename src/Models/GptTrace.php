<?php

namespace MalteKuhr\LaravelGpt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use MalteKuhr\LaravelGpt\Contracts\InputPart;
use MalteKuhr\LaravelGpt\GptAction;
use MalteKuhr\LaravelGpt\Data\ModelResponse;

class GptTrace extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'attributes' => 'array',
        'model_response' => 'array',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            $model->connection = config('laravel-gpt.database.connection');
        });

        static::retrieved(function ($model) {
            $model->connection = config('laravel-gpt.database.connection');
        });

        static::deleting(function ($model) {
            $model->connection = config('laravel-gpt.database.connection');
        });
    }

    /**
     * Get and set the input attribute.
     */
    protected function input(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => array_map(function (array $part) {
                $className = 'MalteKuhr\\LaravelGpt\\Implementations\\Parts\\Input' . ucfirst($part['type']);
                return $className::fromArray($part);
            }, json_decode($value, true)),
            set: fn ($value) => json_encode(array_map(function (InputPart $part) {
                return $part->toArray();
            }, $value))
        );
    }

    /**
     * Create a trace of the GPT action and its response.
     *
     * @param GptAction $action
     * @param ModelResponse $response
     * @return self
     */
    public static function trace(GptAction $action, ModelResponse $response): self
    {
        return self::create([
            'class' => get_class($action),
            'input' => $action->parts(),
            'model_response' => $response->toArray(),
            'meta' => $action->meta(),
            'attributes' => $action->attributes()
        ]);
    }

    /**
     * Get the action instance associated with this trace.
     *
     * @return GptAction|null
     */
    public function getAction(): ?GptAction
    {
        $actionClass = $this->class;
        if (!class_exists($actionClass) || !is_subclass_of($actionClass, GptAction::class)) {
            return null;
        }

        return $actionClass::make($this->input, $this->attributes, $this->meta, ModelResponse::fromArray($this->model_response));
    }
}