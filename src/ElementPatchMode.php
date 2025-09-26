<?php

namespace DatastarSwoole;

/**
 * Wrapper enum for starfederation\datastar\enums\ElementPatchMode
 */
enum ElementPatchMode: string
{
    // Morphs the element into the existing element.
    case Outer = 'outer';

    // Replaces the inner HTML of the existing element.
    case Inner = 'inner';

    // Removes the existing element.
    case Remove = 'remove';

    // Replaces the existing element with the new element.
    case Replace = 'replace';

    // Prepends the element inside to the existing element.
    case Prepend = 'prepend';

    // Appends the element inside the existing element.
    case Append = 'append';

    // Inserts the element before the existing element.
    case Before = 'before';

    // Inserts the element after the existing element.
    case After = 'after';
}