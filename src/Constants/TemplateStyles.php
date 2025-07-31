<?php

namespace Ingenius\Core\Constants;

class TemplateStyles
{
    public const BASIC_STYLES = [
        [
            'key' => 'navbar',
            'selected' => 'Navbar',
            'componentsOptions' => [
                'SimpleNavbar',
                'Navbar'
            ],
            'colorProperties' => [
                [
                    'key' => '--navbar-bg-color',
                    'label' => 'Navbar Background Color',
                    'value' => '#fff'
                ],
                [
                    'key' => '--navbar-text-color',
                    'label' => 'Navbar Text Color',
                    'value' => '#808080'
                ],
                [
                    'key' => '--shopcart-icon-indicator-text',
                    'label' => 'ShopCart Indicator Text Color',
                    'value' => '#fff'
                ],
                [
                    'key' => '--shopcart-icon-indicator',
                    'label' => 'ShopCart Indicator Background Color',
                    'value' => '#ff0000'
                ]
            ]
        ],
        [
            'key' => 'submenuHeader',
            'selected' => 'SubMenuHeader',
            'componentsOptions' => [
                'SimpleSubMenuHeader',
                'SubMenuHeader'
            ],
            'colorProperties' => [
                [
                    'key' => '--submenu-header-bg-color',
                    'label' => 'Submenu Header Background Color',
                    'value' => '#fff'
                ],
                [
                    'key' => '--submenu-header-border-color',
                    'label' => 'Submenu Header Border Color',
                    'value' => '#e0e0e0'
                ]
            ]
        ],
        [
            'key' => 'submenu',
            'selected' => 'SubMenu',
            'componentsOptions' => [
                'SimpleSubMenu',
                'SubMenu'
            ],
            'colorProperties' => [
                [
                    'key' => '--submenu-text-color',
                    'label' => 'Submenu Text Color',
                    'value' => '#808080'
                ],
                [
                    'key' => '--submenu-hover-text-color',
                    'label' => 'Submenu Hover Text Color',
                    'value' => '#000'
                ],
                [
                    'key' => '--submenu-bg-color',
                    'label' => 'Submenu Background Color',
                    'value' => '#fff'
                ],
                [
                    'key' => '--submenu-hover-bg-color',
                    'label' => 'Submenu Hover Background Color',
                    'value' => '#fff'
                ],
                [
                    'key' => '--submenu-border-color',
                    'label' => 'Submenu Border Color',
                    'value' => 'transparent'
                ],
                [
                    'key' => '--submenu-main-text-color',
                    'label' => 'Submenu Main Text Color',
                    'value' => '#808080'
                ],
                [
                    'key' => '--submenu-main-bg-color',
                    'label' => 'Submenu Main Background Color',
                    'value' => '#fff'
                ],
                [
                    'key' => '--submenu-main-hover-bg-color',
                    'label' => 'Submenu Main Hover Background Color',
                    'value' => '#fff'
                ],
                [
                    'key' => '--submenu-main-hover-text-color',
                    'label' => 'Submenu Main Hover Text Color',
                    'value' => '#000'
                ],
                [
                    'key' => '--submenu-main-border-color',
                    'label' => 'Submenu Main Border Color',
                    'value' => 'transparent'
                ]
            ]
        ],
        [
            'key' => 'card',
            'selected' => 'ProductCard',
            'componentsOptions' => [
                'SimpleProductCard',
                'ProductCard',
                'ActionProductCard'
            ],
            'colorProperties' => []
        ]
    ];
}
