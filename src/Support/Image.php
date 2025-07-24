<?php

namespace Ingenius\Core\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * @author Abel David.
 */
class Image implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * @param int $id
     * @param string $origin
     * @param string $thumb
     * @param string $rectangle
     * @param string $type
     * @param int $size
     */
    public function __construct(
        public int $id,
        public string $origin,
        public string $thumb,
        public string $rectangle,
        public string $type,
        public int $size
    ) {
        //
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'origin' => $this->origin,
            'thumb' => $this->thumb,
            'rectangle' => $this->rectangle,
            'type' => $this->type,
            'size' => $this->size
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
