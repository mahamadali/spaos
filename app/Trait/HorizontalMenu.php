<?php

namespace App\Trait;

trait HorizontalMenu
{
    protected function staticMenu($menu, $data)
    {
        $menu->add('
                <span class="default-icon">'.$data['title'] ?? '-'.'</span>
                <span class="mini-icon">-</span>
            ', [
            'url' => '#',
            'class' => 'nav-item static-item',
        ])
            ->data(['order' => $data['order'] ?? 0])
            ->link->attr([
                'class' => 'nav-link static-item disabled',
            ]);
    }

    protected function mainRoute($menu, $data)
    {
        $menuData = [];

        if (isset($data['route'])) {
            $menuData['route'] = $data['route'];
        } elseif (isset($data['url'])) {
            $menuData['url'] = $data['url'] ?? '#';
        } else {
            $menuData['route'] = 'login';
        }

        $linkData = ['class' => 'nav-link'];

        if (isset($data['target']) && $data['target']) {
            $linkData['target'] = $data['target'];
        }

        $menuData['class'] = 'nav-item';

        $menu->add($this->createMenuTitle($data['title'] ?? ''), $menuData)
            ->data([
                'order' => $data['order'] ?? 0,
                'activematches' => $data['active'] ?? '',
                'permission' => $data['permission'] ?? [],
            ])
            ->prepend($this->createMenuIcon($data['icon'] ?? ''))
            ->append($this->createMenuIcon($data['sub_icon'] ?? ''))
            ->link->attr($linkData);
    }

    protected function parentMenu($menu, $data)
    {
        $sub_menu = $menu->add($this->createMenuTitle($data['title'] ?? ''), ['class' => $data['li_class'] ?? 'nav-item'])
            ->nickname($data['nickname'])
            ->data([
                'order' => $data['order'] ?? 0,
                'activematches' => $data['active'] ?? '',
                'permission' => $data['permission'] ?? [],
            ])
            ->prepend($this->createMenuIcon($data['icon'] ?? null));

        $sub_menu->link->attr([
            'class' => $data['a_class'] ?? 'nav-link',
            'href' => '#'.$data['nickname'] ?? 'sidemenu',
            'data-bs-parent' => $data['parent'] ?? '#sidebar-menu',
        ]);
        $sub_menu->url('#'.$data['nickname'] ?? 'sidemenu');

        return $sub_menu;
    }

    protected function childMain($menu, $data)
    {
        $menu->add($this->createMenuTitle($data['title']), [
            'route' => $data['route'],
            'class' => $data['li_class'] ?? 'nav-item',
        ])
            ->data([
                'order' => $data['order'] ?? 0,
                'activematches' => $data['active'] ?? '',
                'permission' => $data['permission'] ?? [],
            ])
            ->prepend($this->createMenuIcon($data['icon'] ?? null))
            ->link->attr(['class' => $data['a_class'] ?? 'nav-link']);
    }

    protected function popupMenu($menu, $data)
    {
        $menu->add($this->createMenuTitle($data['title']), [
            'url' => 'javascript:void(0)',
            'class' => 'nav-item',
            'data-bs-toggle' => $data['extra']['toggle'],
            'data-bs-target' => $data['extra']['target'],
        ])
            ->data([
                'order' => $data['order'] ?? 0,
                'activematches' => $data['active'] ?? '',
                'permission' => $data['permission'] ?? [],
            ])
            ->link->attr(['class' => 'nav-link']);
    }

    protected function createMenuTitle($title)
    {
        return "<span class='nav-text ms-2'>$title</span>";
    }

    protected function createMenuIcon($cutomeIcon = null)
    {
        $icon = '';

        if (isset($cutomeIcon)) {
            $icon = $cutomeIcon;
        }

        return $icon;
    }

    public function createCompanyMenu($menu)
    {
        $huimenu = \Menu::get('horizontal_menu');
        
    }
}
