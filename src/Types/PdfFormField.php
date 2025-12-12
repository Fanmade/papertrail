<?php

namespace Vqs\Papertrail\Types;

readonly class PdfFormField
{
    public function __construct(
        public string $name,
        public string $value,
        public int $page = 1,
        public Rect $rect = new Rect(0, 0, 0, 0),
        public string $type = PdfFieldType::TEXT->value,
    ) {}

    /**
     * @param  array<string, int|string|array<string,float>>  $data
     */
    public static function fromArray(array $data): self
    {
        if (isset($data['rect']) && is_array($data['rect'])) {
            $data['rect'] = Rect::fromArray($data['rect']);
        }
        //        if (isset($data['type']) && is_string($data['type'])) {
        //            $data['type'] = PdfFieldType::tryFrom($data['type']) ?? PdfFieldType::TEXT;
        //        }

        return new self(...$data);
    }

    /**
     * @return array<string, int|string|array<string,float>>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'page' => $this->page,
            'rect' => $this->rect->toArray(),
            'type' => $this->type->value,
        ];
    }
}
