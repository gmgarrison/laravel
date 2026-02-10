<?php

namespace App\Traits;

use BackedEnum;
use Illuminate\Support\Facades\DB;

trait HasEnums
{
    public function updateDatabaseComments(): void
    {
        foreach ($this->getCasts() as $attribute => $enumClass) {
            if (class_exists($enumClass) && is_subclass_of($enumClass, BackedEnum::class)) {
                $text = $enumClass::databaseComment();
                $quotedText = DB::getPdo()->quote($text);
                // Since our query already includes single quotes around the comment,
                // remove the extra surrounding quotes:
                $escapedText = substr(substr($quotedText, 1, -1), 0, 1000);

                $tableName = $this->getTable();

                DB::statement("ALTER TABLE $tableName MODIFY COLUMN $attribute TINYINT UNSIGNED COMMENT '$escapedText'");
            }
        }
    }
}
