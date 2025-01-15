<?php

namespace MalteKuhr\LaravelGpt\Implementations\Parts;

use MalteKuhr\LaravelGpt\Contracts\InputPart;
use Illuminate\Support\Facades\Storage;

class InputFile implements InputPart
{
    /**
     * @param string $mimeType The MIME type of the file.
     * @param string $content Base64 encoded content of the file.
     */
    public function __construct(
        public readonly string $extension,
        public readonly string $content
    ) {}

    /**
     * Create a ChatFile instance from a URL.
     *
     * @param string $url
     * @return static
     */
    public static function fromUrl(string $url): static
    {
        $content = file_get_contents($url);
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        return new static($extension, base64_encode($content));
    }

    /**
     * Create a ChatFile instance from a file on the disk.
     *
     * @param string $path
     * @return static
     */
    public static function fromDisk(string $path, ?string $disk = null): static
    {
        $disk = Storage::disk($disk ?? config('laravel-gpt.disk.name'));

        $content = $disk->get($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return new static($extension, base64_encode($content));
    }

    /**
     * Ensure the file is stored on the disk.
     *
     * @return void
     */
    public function ensureStored(): void
    {
        $disk = Storage::disk(config('laravel-gpt.disk.name'));

        if (!$disk->exists($this->filePath())) {
            $disk->put(
                $this->filePath(), 
                base64_decode($this->content)
            );
        }
    }

    /**
     * Convert the message part to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $this->ensureStored();

        return [
            'path' => $this->filePath(),
        ];
    }

    /**
     * Create a message part from an array.
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $disk = Storage::disk(config('laravel-gpt.disk.name'));
        $content = base64_encode($disk->get($data['path']));
        $extension = pathinfo($data['path'], PATHINFO_EXTENSION);

        return new static($extension, $content);
    }

    /**
     * Get the file content.
     *
     * @return string
     */
    public function getFile(): string
    {
        return base64_decode($this->content);
    }

    /**
     * Get the file content. (Base64 encoded)
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get the MIME type of the file.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer(base64_decode($this->content));
    }

    /**
     * Get the file URL.
     *
     * @return string
     */
    public function getFileUrl(): string
    {
        $disk = Storage::disk(config('laravel-gpt.disk.name'));
        $driver = config('filesystems.disks.' . config('laravel-gpt.disk.name') . '.driver');

        if ($driver === 's3') {
            return $disk->temporaryUrl($this->filePath(), now()->addMinutes(15));
        }
        
        return $disk->url($this->filePath());
    }

    /**
     * Generate SHA1 hash for the content.
     *
     * @return string
     */
    private function sha(): string
    {
        return sha1($this->content);
    }

    /**
     * Get the file path on the disk.
     *
     * @return string
     */
    private function filePath(): string
    {
        $prefix = config('laravel-gpt.disk.prefix');

        return "{$prefix}/{$this->sha()}.{$this->extension}";
    }
}