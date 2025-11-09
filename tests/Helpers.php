<?php

function existsElement($element, $old, $new, $action, $title) {
    return $element->filter(function ($item) use ($old, $new, $action, $title) {
        return $item['title'] === $title
            && $item['action'] === $action
            && $item['old'] == $old
            && $item['new'] == $new;
    })->isNotEmpty();
}
