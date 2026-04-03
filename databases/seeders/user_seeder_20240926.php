<?php

declare(strict_types=1);
use App\Model\Permission\Role;
use App\Model\Permission\User;
use Hyperf\Database\Seeders\Seeder;

class UserSeeder20240926 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        /** @var Role $role */
        $role = Role::query()->firstOrCreate(
            ['code' => 'SuperAdmin'],
            [
                'name' => '超级管理员',
                'status' => 1,
                'sort' => 0,
                'created_by' => 0,
                'updated_by' => 0,
                'remark' => '超级管理员',
            ]
        );

        /** @var User $entity */
        $entity = User::query()->firstOrNew(['username' => 'admin']);
        $entity->fill([
            'user_type' => '100',
            'nickname' => '创始人',
            'email' => 'admin@adminmine.com',
            'phone' => '16858888988',
            'signed' => '广阔天地，大有所为',
            'created_by' => 0,
            'updated_by' => 0,
            'status' => 1,
            'created_at' => $entity->exists ? $entity->created_at : $now,
            'updated_at' => $now,
        ]);
        $entity->password = 123456;
        $entity->save();

        $entity->roles()->sync([$role->id]);
    }
}
