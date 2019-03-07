<?php
/**
 * Created by PhpStorm.
 * User: bryan.yen
 * Date: 2018/10/25
 * Time: 9:40 PM
 */

namespace App\Repositories;

use App\Entities\KerryPackageStatus;

class PackageStatusRepository
{
    public function index()
    {
        return KerryPackageStatus::get();
    }

    public function create(array $data)
    {
        return KerryPackageStatus::create($data);
    }

    public function find($id)
    {
        return KerryPackageStatus::find($id);
    }

    public function delete($id)
    {
        return KerryPackageStatus::destroy($id);
    }

    public function update($id, array $data)
    {
        $post = KerryPackageStatus::find($id);

        if (!$post) {
            return false;
        }

        return $post->update($data);
    }
}