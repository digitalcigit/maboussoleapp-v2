<?php

namespace App\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EnumType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'ENUM(' . implode(', ', array_map(fn ($val) => "'$val'", $column['allowed'])) . ')';
    }

    public function getName()
    {
        return 'enum';
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform)
    {
        return ['enum'];
    }
}
