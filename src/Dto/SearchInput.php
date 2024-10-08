<?php

namespace App\Dto;

class SearchInput
{
    public \DateTimeImmutable|null $date = null;

    public ?string $keyword = null;
}
