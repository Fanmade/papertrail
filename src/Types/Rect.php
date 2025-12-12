<?php

namespace Vqs\Papertrail\Types;

readonly class Rect
{
    public function __construct(
        public float $x,
        public float $y,
        public float $width,
        public float $height,
    ) {}

    /**
     * @param  array<string, float>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
