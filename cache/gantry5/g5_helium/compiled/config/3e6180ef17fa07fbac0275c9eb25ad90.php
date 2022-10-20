<?php
return [
    '@class' => 'Gantry\\Component\\Config\\CompiledConfig',
    'timestamp' => 1666200081,
    'checksum' => 'e26edefea29530f7b14d1a0f5b32a59d',
    'files' => [
        'templates/g5_helium/custom/config/12' => [
            'assignments' => [
                'file' => 'templates/g5_helium/custom/config/12/assignments.yaml',
                'modified' => 1652181610
            ],
            'index' => [
                'file' => 'templates/g5_helium/custom/config/12/index.yaml',
                'modified' => 1662627756
            ],
            'layout' => [
                'file' => 'templates/g5_helium/custom/config/12/layout.yaml',
                'modified' => 1662627756
            ],
            'page/body' => [
                'file' => 'templates/g5_helium/custom/config/12/page/body.yaml',
                'modified' => 1659363800
            ],
            'page/fontawesome' => [
                'file' => 'templates/g5_helium/custom/config/12/page/fontawesome.yaml',
                'modified' => 1659363800
            ],
            'page/head' => [
                'file' => 'templates/g5_helium/custom/config/12/page/head.yaml',
                'modified' => 1659363800
            ],
            'styles' => [
                'file' => 'templates/g5_helium/custom/config/12/styles.yaml',
                'modified' => 1652181610
            ]
        ],
        'templates/g5_helium/custom/config/default' => [
            'index' => [
                'file' => 'templates/g5_helium/custom/config/default/index.yaml',
                'modified' => 1652960154
            ],
            'layout' => [
                'file' => 'templates/g5_helium/custom/config/default/layout.yaml',
                'modified' => 1652181610
            ],
            'page/assets' => [
                'file' => 'templates/g5_helium/custom/config/default/page/assets.yaml',
                'modified' => 1655754534
            ],
            'page/body' => [
                'file' => 'templates/g5_helium/custom/config/default/page/body.yaml',
                'modified' => 1655754534
            ],
            'page/fontawesome' => [
                'file' => 'templates/g5_helium/custom/config/default/page/fontawesome.yaml',
                'modified' => 1655754534
            ],
            'page/head' => [
                'file' => 'templates/g5_helium/custom/config/default/page/head.yaml',
                'modified' => 1655754534
            ],
            'styles' => [
                'file' => 'templates/g5_helium/custom/config/default/styles.yaml',
                'modified' => 1652181610
            ]
        ]
    ],
    'data' => [
        'particles' => [
            'contentcubes' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true
            ],
            'contenttabs' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true,
                'animation' => 'slide'
            ],
            'copyright' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true,
                'date' => [
                    'start' => 'now',
                    'end' => 'now'
                ],
                'target' => '_blank'
            ],
            'horizontalmenu' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true,
                'target' => '_blank'
            ],
            'owlcarousel' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true,
                'nav' => 'disable',
                'dots' => 'enable',
                'autoplay' => 'disable',
                'imageOverlay' => 'enable'
            ],
            'branding' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true,
                'content' => 'Powered by <a href="http://www.gantry.org/" title="Gantry Framework" class="g-powered-by">Gantry Framework</a>',
                'css' => [
                    'class' => 'branding'
                ]
            ],
            'custom' => [
                'caching' => [
                    'type' => 'config_matches',
                    'values' => [
                        'twig' => '0',
                        'filter' => '0'
                    ]
                ],
                'enabled' => true,
                'twig' => '0',
                'filter' => '0'
            ],
            'logo' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true,
                'target' => '_self',
                'link' => true
            ],
            'menu' => [
                'caching' => [
                    'type' => 'menu'
                ],
                'enabled' => true,
                'menu' => '',
                'base' => '/',
                'startLevel' => 1,
                'maxLevels' => 0,
                'renderTitles' => 0,
                'hoverExpand' => 1,
                'mobileTarget' => 0,
                'forceTarget' => 0
            ],
            'mobile-menu' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true
            ],
            'social' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true,
                'css' => [
                    'class' => 'social'
                ],
                'target' => '',
                'display' => 'both'
            ],
            'spacer' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true
            ],
            'totop' => [
                'caching' => [
                    'type' => 'static'
                ],
                'enabled' => true,
                'css' => [
                    'class' => 'totop'
                ]
            ],
            'analytics' => [
                'enabled' => true,
                'ua' => [
                    'anonym' => false
                ]
            ],
            'assets' => [
                'enabled' => true
            ],
            'content' => [
                'enabled' => true
            ],
            'contentarray' => [
                'enabled' => true,
                'article' => [
                    'filter' => [
                        'featured' => ''
                    ],
                    'limit' => [
                        'total' => 2,
                        'columns' => 2,
                        'start' => 0
                    ],
                    'display' => [
                        'pagination_buttons' => '',
                        'image' => [
                            'enabled' => 'intro'
                        ],
                        'text' => [
                            'type' => 'intro',
                            'limit' => '',
                            'formatting' => 'text',
                            'prepare' => false
                        ],
                        'edit' => false,
                        'title' => [
                            'enabled' => 'show'
                        ],
                        'date' => [
                            'enabled' => 'published',
                            'format' => 'l, F d, Y'
                        ],
                        'read_more' => [
                            'enabled' => 'show'
                        ],
                        'author' => [
                            'enabled' => 'show'
                        ],
                        'category' => [
                            'enabled' => 'link'
                        ],
                        'hits' => [
                            'enabled' => 'show'
                        ]
                    ],
                    'sort' => [
                        'orderby' => 'publish_up',
                        'ordering' => 'ASC'
                    ]
                ]
            ],
            'date' => [
                'enabled' => true,
                'css' => [
                    'class' => 'date'
                ],
                'date' => [
                    'formats' => 'l, F d, Y'
                ]
            ],
            'frameworks' => [
                'enabled' => true,
                'jquery' => [
                    'enabled' => 0,
                    'ui_core' => 0,
                    'ui_sortable' => 0
                ],
                'bootstrap' => [
                    'enabled' => 0
                ],
                'mootools' => [
                    'enabled' => 0,
                    'more' => 0
                ]
            ],
            'lightcase' => [
                'enabled' => true
            ],
            'messages' => [
                'enabled' => true
            ],
            'module' => [
                'enabled' => true
            ],
            'position' => [
                'enabled' => true
            ]
        ],
        'styles' => [
            'above' => [
                'background' => '#f4f5f7',
                'text-color' => '#2a363b'
            ],
            'accent' => [
                'color-1' => '#a8a7a7',
                'color-2' => '#474747'
            ],
            'base' => [
                'background' => '#ffffff',
                'text-color' => '#253544'
            ],
            'breakpoints' => [
                'large-desktop-container' => '75rem',
                'desktop-container' => '60rem',
                'tablet-container' => '48rem',
                'large-mobile-container' => '30rem',
                'mobile-menu-breakpoint' => '48rem'
            ],
            'expanded' => [
                'background' => '#ffffff',
                'text-color' => '#2a363b'
            ],
            'features' => [
                'background' => '#ffffff',
                'text-color' => '#2a363b'
            ],
            'font' => [
                'family-default' => 'family=Open+Sans:300,600,700,800,400&subset=cyrillic,latin',
                'family-title' => 'family=Open+Sans:300,600,700,800,400&subset=cyrillic,latin'
            ],
            'footer' => [
                'background' => '#253544',
                'text-color' => '#ffffff'
            ],
            'header' => [
                'background' => '#474747',
                'background-image' => 'gantry-media://header/img01.jpg',
                'background-overlay' => 'enabled',
                'text-color' => '#eceeef'
            ],
            'intro' => [
                'background' => '',
                'text-color' => '#2a363b'
            ],
            'link' => [
                'regular' => '#125ab8',
                'hover' => '#2a363b'
            ],
            'menu' => [
                'col-width' => '180px',
                'animation' => 'g-fade',
                'hide-on-mobile' => '0'
            ],
            'navigation' => [
                'background' => '#253544',
                'text-color' => '#ffffff'
            ],
            'offcanvas' => [
                'background' => '#f0fbff',
                'text-color' => '#000000',
                'toggle-color' => '#ffffff',
                'toggle-visibility' => '1',
                'width' => '18rem'
            ],
            'testimonials' => [
                'background' => '#a8a7a7',
                'background-image' => 'gantry-media://testimonials/img01.jpg',
                'background-overlay' => 'enabled',
                'text-color' => '#eceeef'
            ],
            'utility' => [
                'background' => '#474747',
                'text-color' => '#ffffff'
            ],
            'preset' => 'preset5'
        ],
        'page' => [
            'body' => [
                'attribs' => [
                    'class' => 'gantry g-helium-style',
                    'id' => '',
                    'extra' => [
                        
                    ]
                ],
                'layout' => [
                    'sections' => '0'
                ],
                'body_top' => '',
                'body_bottom' => ''
            ],
            'fontawesome' => [
                'enable' => '1',
                'version' => 'fa5js',
                'fa4_compatibility' => '0',
                'content_compatibility' => '1',
                'html_css_import' => '',
                'html_js_import' => ''
            ],
            'assets' => [
                'favicon' => 'gantry-media://L1.jpg',
                'touchicon' => '',
                'css' => [
                    
                ],
                'javascript' => [
                    
                ]
            ],
            'head' => [
                'meta' => [
                    
                ],
                'head_bottom' => ' <script src="https://api-maps.yandex.ru/2.1/?apikey=a4691c36-e9ad-4a81-96ed-a185938751a2&lang=ru_RU" type="text/javascript ">  </script>',
                'atoms' => [
                    0 => [
                        'id' => 'assets-1718',
                        'type' => 'assets',
                        'title' => 'Custom CSS / JS',
                        'attributes' => [
                            'enabled' => '1',
                            'css' => [
                                0 => [
                                    'location' => 'gantry-assets://custom/scss/header-js.css',
                                    'inline' => '',
                                    'extra' => [
                                        
                                    ],
                                    'priority' => '0',
                                    'name' => 'header-js'
                                ]
                            ],
                            'javascript' => [
                                0 => [
                                    'location' => '',
                                    'inline' => 'jQuery( document ).ready(function() {
   jQuery(\'#nav-icon3\').click(function(){
       jQuery(this).toggleClass(\'open\');
    });
});',
                                    'in_footer' => '0',
                                    'extra' => [
                                        
                                    ],
                                    'priority' => '0',
                                    'name' => 'бургер меню'
                                ],
                                1 => [
                                    'location' => 'gantry-assets://js/custom.js',
                                    'inline' => '',
                                    'in_footer' => '0',
                                    'extra' => [
                                        
                                    ],
                                    'priority' => '0',
                                    'name' => 'custom'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'index' => [
            'name' => '12',
            'timestamp' => 1662627756,
            'version' => 7,
            'preset' => [
                'image' => 'gantry-admin://images/layouts/default.png',
                'name' => 'default',
                'timestamp' => 1649755126
            ],
            'positions' => [
                'Kabinet' => 'Module Position',
                'module-position1' => 'Module Position',
                'svg' => 'SVG',
                'Slider' => 'Module Position',
                'Cobalt-categories' => 'Module Position',
                'aside' => 'Aside',
                'sidebar' => 'Sidebar',
                'bottombar' => 'Module Position',
                'FOR-Notifications' => 'Module Position'
            ],
            'sections' => [
                'navigation' => 'Navigation',
                'intro' => 'Intro',
                'features' => 'Features',
                'utility' => 'Utility',
                'above' => 'Above',
                'testimonials' => 'Testimonials',
                'expanded' => 'Expanded',
                'header' => 'Header',
                'aside' => 'Aside',
                'sidebar' => 'Sidebar',
                'mainbar' => 'Mainbar',
                'footer' => 'Footer',
                'offcanvas' => 'Offcanvas'
            ],
            'particles' => [
                'module' => [
                    'position-module-7565' => 'Module Instance',
                    'position-module-9086' => 'Поиск'
                ],
                'logo' => [
                    'logo-9608' => 'Logo / Image',
                    'logo-5665' => 'Logo / Image'
                ],
                'position' => [
                    'position-position-3779' => 'Module Position',
                    'position-position-4510' => 'Module Position',
                    'position-position-3589' => 'SVG',
                    'position-position-8621' => 'Module Position',
                    'position-position-9266' => 'Module Position',
                    'position-position-4734' => 'Aside',
                    'position-position-3949' => 'Sidebar',
                    'position-position-1310' => 'Module Position',
                    'position-position-5139' => 'Module Position'
                ],
                'content' => [
                    'system-content-1587' => 'Page Content'
                ],
                'copyright' => [
                    'copyright-1736' => 'Copyright'
                ]
            ],
            'inherit' => [
                
            ]
        ],
        'layout' => [
            'version' => 2,
            'preset' => [
                'image' => 'gantry-admin://images/layouts/default.png',
                'name' => 'default',
                'timestamp' => 1649755126
            ],
            'layout' => [
                '/navigation/' => [
                    0 => [
                        0 => 'position-module-7565 33.3',
                        1 => 'logo-9608 33.3',
                        2 => 'position-position-3779 33.3'
                    ],
                    1 => [
                        0 => 'position-position-4510'
                    ],
                    2 => [
                        0 => 'position-position-3589'
                    ]
                ],
                '/header/' => [
                    
                ],
                '/intro/' => [
                    0 => [
                        0 => 'position-position-8621'
                    ]
                ],
                '/features/' => [
                    0 => [
                        0 => 'position-position-9266'
                    ]
                ],
                '/utility/' => [
                    
                ],
                '/above/' => [
                    
                ],
                '/testimonials/' => [
                    
                ],
                '/expanded/' => [
                    
                ],
                '/container-main/' => [
                    0 => [
                        0 => [
                            'aside 25' => [
                                0 => [
                                    0 => 'position-position-4734'
                                ]
                            ]
                        ],
                        1 => [
                            'mainbar 50' => [
                                0 => [
                                    0 => 'system-content-1587'
                                ]
                            ]
                        ],
                        2 => [
                            'sidebar 25' => [
                                0 => [
                                    0 => 'position-position-3949'
                                ]
                            ]
                        ]
                    ]
                ],
                '/footer/' => [
                    0 => [
                        0 => 'position-position-1310'
                    ],
                    1 => [
                        0 => 'logo-5665'
                    ],
                    2 => [
                        0 => 'copyright-1736'
                    ],
                    3 => [
                        0 => 'position-position-5139'
                    ],
                    4 => [
                        0 => 'position-module-9086'
                    ]
                ],
                'offcanvas' => [
                    
                ]
            ],
            'structure' => [
                'navigation' => [
                    'type' => 'section',
                    'attributes' => [
                        'boxed' => '0',
                        'class' => 'navigation-top',
                        'variations' => ''
                    ]
                ],
                'header' => [
                    'attributes' => [
                        'boxed' => '',
                        'class' => ''
                    ]
                ],
                'intro' => [
                    'type' => 'section',
                    'attributes' => [
                        'boxed' => '2',
                        'class' => 'mainslide',
                        'variations' => ''
                    ]
                ],
                'features' => [
                    'type' => 'section',
                    'attributes' => [
                        'boxed' => ''
                    ]
                ],
                'utility' => [
                    'type' => 'section',
                    'attributes' => [
                        'boxed' => '',
                        'class' => ''
                    ]
                ],
                'above' => [
                    'type' => 'section',
                    'attributes' => [
                        'boxed' => ''
                    ]
                ],
                'testimonials' => [
                    'type' => 'section',
                    'attributes' => [
                        'boxed' => ''
                    ]
                ],
                'expanded' => [
                    'type' => 'section',
                    'attributes' => [
                        'boxed' => ''
                    ]
                ],
                'aside' => [
                    'attributes' => [
                        'class' => ''
                    ],
                    'block' => [
                        'fixed' => '1'
                    ]
                ],
                'mainbar' => [
                    'type' => 'section',
                    'subtype' => 'main'
                ],
                'sidebar' => [
                    'type' => 'section',
                    'subtype' => 'aside',
                    'attributes' => [
                        'class' => ''
                    ],
                    'block' => [
                        'fixed' => '1'
                    ]
                ],
                'container-main' => [
                    'attributes' => [
                        'boxed' => ''
                    ]
                ],
                'footer' => [
                    'attributes' => [
                        'boxed' => ''
                    ]
                ],
                'offcanvas' => [
                    'attributes' => [
                        'position' => 'g-offcanvas-left',
                        'class' => '',
                        'extra' => [
                            
                        ],
                        'swipe' => '0',
                        'css3animation' => '1'
                    ]
                ]
            ],
            'content' => [
                'position-module-7565' => [
                    'title' => 'Module Instance',
                    'attributes' => [
                        'module_id' => '130',
                        'key' => 'module-instance'
                    ],
                    'block' => [
                        'id' => 'gd_block_menu_mobile'
                    ]
                ],
                'logo-9608' => [
                    'title' => 'Logo / Image',
                    'attributes' => [
                        'url' => '/craft',
                        'target' => '_self',
                        'image' => '',
                        'height' => '',
                        'link' => '1',
                        'svg' => '<svg width="72" height="19" viewBox="0 0 72 19" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M8.55078 3.01172C7.73047 3.01172 6.99609 3.16016 6.34766 3.45703C5.70703 3.75391 5.16406 4.18359 4.71875 4.74609C4.27344 5.30078 3.93359 5.97656 3.69922 6.77344C3.46484 7.5625 3.34766 8.45312 3.34766 9.44531C3.34766 10.7734 3.53516 11.918 3.91016 12.8789C4.28516 13.832 4.85547 14.5664 5.62109 15.082C6.38672 15.5898 7.35547 15.8438 8.52734 15.8438C9.25391 15.8438 9.96094 15.7734 10.6484 15.6328C11.3359 15.4922 12.0469 15.3008 12.7812 15.0586V17.4492C12.0859 17.7227 11.3828 17.9219 10.6719 18.0469C9.96094 18.1719 9.14062 18.2344 8.21094 18.2344C6.46094 18.2344 5.00781 17.8711 3.85156 17.1445C2.70312 16.418 1.84375 15.3945 1.27344 14.0742C0.710938 12.7539 0.429688 11.207 0.429688 9.43359C0.429688 8.13672 0.609375 6.94922 0.96875 5.87109C1.32812 4.79297 1.85156 3.86328 2.53906 3.08203C3.22656 2.29297 4.07422 1.6875 5.08203 1.26562C6.09766 0.835937 7.25781 0.621094 8.5625 0.621094C9.42188 0.621094 10.2656 0.71875 11.0938 0.914062C11.9297 1.10156 12.7031 1.37109 13.4141 1.72266L12.4297 4.04297C11.8359 3.76172 11.2148 3.51953 10.5664 3.31641C9.91797 3.11328 9.24609 3.01172 8.55078 3.01172ZM21.2773 0.867188C22.7539 0.867188 23.9727 1.04688 24.9336 1.40625C25.9023 1.76562 26.6211 2.3125 27.0898 3.04688C27.5664 3.78125 27.8047 4.71484 27.8047 5.84766C27.8047 6.69141 27.6484 7.41016 27.3359 8.00391C27.0234 8.59766 26.6133 9.08984 26.1055 9.48047C25.5977 9.87109 25.0547 10.1836 24.4766 10.418L29.3398 18H26.1641L22.0273 11.1328H19.2734V18H16.4609V0.867188H21.2773ZM21.0898 3.21094H19.2734V8.8125H21.2188C22.5234 8.8125 23.4688 8.57031 24.0547 8.08594C24.6484 7.60156 24.9453 6.88672 24.9453 5.94141C24.9453 4.94922 24.6289 4.24609 23.9961 3.83203C23.3711 3.41797 22.4023 3.21094 21.0898 3.21094ZM42.4062 18L40.7305 13.2422H34.1797L32.5039 18H29.5391L35.9141 0.796875H39.0312L45.3945 18H42.4062ZM40.0039 10.8281L38.375 6.14062C38.3125 5.9375 38.2227 5.65234 38.1055 5.28516C37.9883 4.91016 37.8711 4.53125 37.7539 4.14844C37.6367 3.75781 37.5391 3.42969 37.4609 3.16406C37.3828 3.48438 37.2852 3.84375 37.168 4.24219C37.0586 4.63281 36.9492 5 36.8398 5.34375C36.7383 5.6875 36.6602 5.95312 36.6055 6.14062L34.9648 10.8281H40.0039ZM50.4453 18H47.6562V0.867188H57.3359V3.23438H50.4453V8.57812H56.8906V10.9336H50.4453V18ZM66.3945 18H63.5703V3.25781H58.5547V0.867188H71.3984V3.25781H66.3945V18Z" fill="white"/>
</svg>
',
                        'text' => '',
                        'class' => ''
                    ],
                    'block' => [
                        'class' => 'logocraft',
                        'variations' => 'title-center center'
                    ]
                ],
                'position-position-3779' => [
                    'title' => 'Module Position',
                    'attributes' => [
                        'key' => 'Kabinet'
                    ],
                    'block' => [
                        'id' => 'kabinet',
                        'class' => 'cabinet'
                    ]
                ],
                'position-position-4510' => [
                    'title' => 'Module Position',
                    'attributes' => [
                        'key' => 'module-position1',
                        'chrome' => ''
                    ]
                ],
                'position-position-3589' => [
                    'title' => 'SVG',
                    'attributes' => [
                        'key' => 'svg'
                    ]
                ],
                'position-position-8621' => [
                    'title' => 'Module Position',
                    'attributes' => [
                        'key' => 'Slider'
                    ]
                ],
                'position-position-9266' => [
                    'title' => 'Module Position',
                    'attributes' => [
                        'key' => 'Cobalt-categories'
                    ],
                    'block' => [
                        'class' => 'cob-cat'
                    ]
                ],
                'position-position-4734' => [
                    'title' => 'Aside',
                    'attributes' => [
                        'key' => 'aside'
                    ]
                ],
                'position-position-3949' => [
                    'title' => 'Sidebar',
                    'attributes' => [
                        'key' => 'sidebar'
                    ]
                ],
                'position-position-1310' => [
                    'title' => 'Module Position',
                    'attributes' => [
                        'key' => 'bottombar'
                    ],
                    'block' => [
                        'id' => 'bottombar',
                        'class' => 'bottombar'
                    ]
                ],
                'logo-5665' => [
                    'title' => 'Logo / Image',
                    'attributes' => [
                        'url' => '/craft',
                        'link' => '1',
                        'svg' => '<svg width="72" height="19" viewBox="0 0 72 19" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M8.55078 3.01172C7.73047 3.01172 6.99609 3.16016 6.34766 3.45703C5.70703 3.75391 5.16406 4.18359 4.71875 4.74609C4.27344 5.30078 3.93359 5.97656 3.69922 6.77344C3.46484 7.5625 3.34766 8.45312 3.34766 9.44531C3.34766 10.7734 3.53516 11.918 3.91016 12.8789C4.28516 13.832 4.85547 14.5664 5.62109 15.082C6.38672 15.5898 7.35547 15.8438 8.52734 15.8438C9.25391 15.8438 9.96094 15.7734 10.6484 15.6328C11.3359 15.4922 12.0469 15.3008 12.7812 15.0586V17.4492C12.0859 17.7227 11.3828 17.9219 10.6719 18.0469C9.96094 18.1719 9.14062 18.2344 8.21094 18.2344C6.46094 18.2344 5.00781 17.8711 3.85156 17.1445C2.70312 16.418 1.84375 15.3945 1.27344 14.0742C0.710938 12.7539 0.429688 11.207 0.429688 9.43359C0.429688 8.13672 0.609375 6.94922 0.96875 5.87109C1.32812 4.79297 1.85156 3.86328 2.53906 3.08203C3.22656 2.29297 4.07422 1.6875 5.08203 1.26562C6.09766 0.835937 7.25781 0.621094 8.5625 0.621094C9.42188 0.621094 10.2656 0.71875 11.0938 0.914062C11.9297 1.10156 12.7031 1.37109 13.4141 1.72266L12.4297 4.04297C11.8359 3.76172 11.2148 3.51953 10.5664 3.31641C9.91797 3.11328 9.24609 3.01172 8.55078 3.01172ZM21.2773 0.867188C22.7539 0.867188 23.9727 1.04688 24.9336 1.40625C25.9023 1.76562 26.6211 2.3125 27.0898 3.04688C27.5664 3.78125 27.8047 4.71484 27.8047 5.84766C27.8047 6.69141 27.6484 7.41016 27.3359 8.00391C27.0234 8.59766 26.6133 9.08984 26.1055 9.48047C25.5977 9.87109 25.0547 10.1836 24.4766 10.418L29.3398 18H26.1641L22.0273 11.1328H19.2734V18H16.4609V0.867188H21.2773ZM21.0898 3.21094H19.2734V8.8125H21.2188C22.5234 8.8125 23.4688 8.57031 24.0547 8.08594C24.6484 7.60156 24.9453 6.88672 24.9453 5.94141C24.9453 4.94922 24.6289 4.24609 23.9961 3.83203C23.3711 3.41797 22.4023 3.21094 21.0898 3.21094ZM42.4062 18L40.7305 13.2422H34.1797L32.5039 18H29.5391L35.9141 0.796875H39.0312L45.3945 18H42.4062ZM40.0039 10.8281L38.375 6.14062C38.3125 5.9375 38.2227 5.65234 38.1055 5.28516C37.9883 4.91016 37.8711 4.53125 37.7539 4.14844C37.6367 3.75781 37.5391 3.42969 37.4609 3.16406C37.3828 3.48438 37.2852 3.84375 37.168 4.24219C37.0586 4.63281 36.9492 5 36.8398 5.34375C36.7383 5.6875 36.6602 5.95312 36.6055 6.14062L34.9648 10.8281H40.0039ZM50.4453 18H47.6562V0.867188H57.3359V3.23438H50.4453V8.57812H56.8906V10.9336H50.4453V18ZM66.3945 18H63.5703V3.25781H58.5547V0.867188H71.3984V3.25781H66.3945V18Z" fill="white"/>
</svg>
'
                    ]
                ],
                'copyright-1736' => [
                    'attributes' => [
                        'date' => [
                            'start' => '2022'
                        ],
                        'owner' => '',
                        'additional' => [
                            'text' => ''
                        ]
                    ]
                ],
                'position-position-5139' => [
                    'title' => 'Module Position',
                    'attributes' => [
                        'key' => 'FOR-Notifications'
                    ],
                    'block' => [
                        'id' => 'notifee'
                    ]
                ],
                'position-module-9086' => [
                    'title' => 'Поиск',
                    'attributes' => [
                        'enabled' => 0,
                        'module_id' => '138'
                    ]
                ]
            ]
        ],
        'assignments' => [
            'menu' => [
                
            ],
            'style' => [
                
            ]
        ]
    ]
];
