<?php

namespace ArchTech\Enums;

trait Strings
{
    /** Get a string from an array in the form: index => element, etc */
    public function toString(): string
    {
        return self::descriptions()[$this->value];
    }

    public static function descriptions(bool $includeEmpty = true): array
    {
        $cases = static::cases();

        $toReturn = [];
        if ($includeEmpty) {
            $toReturn[] = '';
        }

        foreach ($cases as $index => $value) {
            $toReturn[$value()] = self::getText($value);
        }

        return $toReturn;
    }

    public function getLabel(): ?string
    {
        return self::getText($this);
    }

    public static function databaseComment(): string
    {
        $toReturn = [];

        foreach (self::descriptions(false) as $index => $value) {
            $toReturn[] = $index.'=>'.$value;
        }

        return implode(', ', $toReturn);
    }
}
