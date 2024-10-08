<?php

namespace App\Entity;

enum EventType: string
{
    case COMMIT = 'COM';
    case COMMENT = 'MSG';
    case PULL_REQUEST = 'PR';
}
