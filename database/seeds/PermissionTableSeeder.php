<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
      /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $permissions = [
           'role-list',
           'role-create',
           'role-edit',
           'role-delete',
           'rssfeed-list',
           'rssfeed-save-post',
           'blog-list',
           'blog-create',
           'blog-edit',
           'blog-delete',
           'rss-feed-list',
           'rss-feed-create',
           'rss-feed-edit',
           'rss-feed-delete',
           'cms-pages-list',
           'cms-pages-edit',
           'user-list',
           'user-delete',
            'sub-admin-list',
            'sub-admin-create',
            'sub-admin-edit',
            'sub-admin-delete',
            'search-log-list',
            'setting-list',
            'setting-edit',
            'category-list',
            'category-create',
            'category-edit',
            'category-delete',
            'author-list',
            'author-create',
            'author-edit',
            'author-delete',
            'news-ads',
            'news-create-ads'

        ];
        foreach ($permissions as $permissions) {
           
             Permission::create(['name' => $permission]);
        }
    }
}
