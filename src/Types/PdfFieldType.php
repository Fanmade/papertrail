<?php

namespace Fanmade\Papertrail\Types;

enum PdfFieldType: string
{
    case TEXT = 'text';
    case FILE = 'file';
    case DATE = 'date';
    case NUMBER = 'number';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case BUTTON = 'button';
}
