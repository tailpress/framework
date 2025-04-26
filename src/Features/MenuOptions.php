<?php

namespace TailPress\Framework\Features;

class MenuOptions
{
    public function __construct()
    {
        add_filter('nav_menu_css_class', [$this, 'addLiClass'], 10, 4);

        add_filter('nav_menu_submenu_css_class', [$this, 'addSubmenuClass'], 10, 3);
    }

    /**
     * Adds option 'li_class' to 'wp_nav_menu'.
     *
     * @param  string  $classes  String of classes.
     * @param  mixed  $item  The current item.
     * @param  WP_Term  $args  Holds the nav menu arguments.
     * @return array
     */
    public function addLiClass($classes, $item, $args, $depth)
    {
        if (isset($args->li_class)) {
            $classes[] = $args->li_class;
        }

        if (isset($args->{"li_class_$depth"})) {
            $classes[] = $args->{"li_class_$depth"};
        }

        return $classes;
    }

    /**
     * Adds option 'submenu_class' to 'wp_nav_menu'.
     *
     * @param  string  $classes  String of classes.
     * @param  mixed  $item  The current item.
     * @param  WP_Term  $args  Holds the nav menu arguments.
     * @return array
     */
    public function addSubmenuClass($classes, $args, $depth)
    {
        if (isset($args->submenu_class)) {
            $classes[] = $args->submenu_class;
        }

        if (isset($args->{"submenu_class_$depth"})) {
            $classes[] = $args->{"submenu_class_$depth"};
        }

        return $classes;
    }
}
