<?php

//管理后台导航栏
return [
    'home'        => [
        'module' => 'home',
        'name'   => '首页',
        'child'  => [
//            [
//                'parent' => 'article',
//                'name'   => '撰写文章',
//                'module' => 'create'
//            ],
//            [
//                'parent' => 'article',
//                'name'   => '文章列表',
//                'module' => 'list'
//            ],
//            [
//                'parent' => 'application',
//                'name'   => '撰写应用',
//                'module' => 'create'
//            ]
        ]
    ],
    'article'     => [
        'module' => 'article',
        'name'   => '文章',
        'child'  => [
            [
                'parent' => 'article',
                'name'   => '撰写文章',
                'module' => 'create'
            ],
            [
                'parent' => 'article',
                'name'   => '文章列表',
                'module' => 'list'
            ],
            [
                'parent' => 'article',
                'name'   => '文章分类',
                'module' => 'category'
            ],
            [
                'parent' => 'article',
                'name'   => '文章评论',
                'module' => 'comment'
            ],
            [
                'parent' => 'article',
                'name'   => '文章同步',
                'module' => 'articleSyn'
            ],
        ]
    ],
    'application' => [
        'module' => 'application',
        'name'   => '应用',
        'child'  => [
            [
                'parent' => 'application',
                'name'   => '添加应用',
                'module' => 'create'
            ],
            [
                'parent' => 'application',
                'name'   => '应用列表',
                'module' => 'list'
            ],
            [
                'parent' => 'application',
                'name'   => '应用分类',
                'module' => 'category'
            ]
        ]
    ],
    'userManage'  => [
        'module' => 'userManage',
        'name'   => '用户',
        'child'  => [
            [
                'parent' => 'userManage',
                'name'   => '添加用户',
                'module' => 'user/create'
            ],
            [
                'parent' => 'userManage',
                'name'   => '用户列表',
                'module' => 'user/list'
            ],
            [
                'parent' => 'userManage',
                'name'   => '添加角色',
                'module' => 'role/create'
            ],
            [
                'parent' => 'userManage',
                'name'   => '角色列表',
                'module' => 'role/list'
            ],
        ]
    ],
    'plugin'      => [
        'module' => 'plugin',
        'name'   => '插件',
        'child'  => [
            [
                'parent' => 'plugin',
                'name'   => '插件列表',
                'module' => 'pluginList'
            ],
            [
                'parent' => 'plugin',
                'name'   => 'Appkey设置',
                'module' => 'appkeyList'
            ],
            [
                'parent' => 'plugin',
                'name'   => '广告设置',
                'module' => 'adList'
            ],
        ]
    ],
    'stat'        => [
        'module' => 'stat',
        'name'   => '统计',
        'child'  => [
            [
                'parent' => 'stat',
                'name'   => '访问量',
                'module' => 'pv'
            ],
            [
                'parent' => 'stat',
                'name'   => '文章绩效统计',
                'module' => 'quality'
            ],
            [
                'parent' => 'stat',
                'name'   => '分类统计',
                'module' => 'appdown'
            ],
            [
                'parent' => 'stat',
                'name'   => '平台统计',
                'module' => 'platform'
            ],
            [
                'parent' => 'stat',
                'name'   => 'API统计',
                'module' => 'api'
            ],
        ]
    ],
    'set'         => [
        'module' => 'set',
        'name'   => '设置',
        'child'  => [
            [
                'parent' => 'set',
                'name'   => '同步设置',
                'module' => 'setSynchronous'
            ],
            [
                'parent' => 'set',
                'name'   => '系统设置',
                'module' => 'settingSystem'
            ],
            [
                'parent' => 'set',
                'name'   => '清空缓存',
                'module' => 'clearCache'
            ],
        ]
        ]];
