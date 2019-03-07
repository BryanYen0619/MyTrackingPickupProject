<?php
/**
 * Created by PhpStorm.
 * User: bryan.yen
 * Date: 2018/9/27
 * Time: 上午11:51
 */

namespace App\Repositories;


use App\Entities\KerryPackageInfo;

class PackageInfoRepository
{
    public function index()
    {
        return KerryPackageInfo::get();
    }

    public function create(array $data)
    {
        return KerryPackageInfo::create($data);
    }

    public function find($id)
    {
        return KerryPackageInfo::find($id);
    }

    public function delete($id)
    {
        return KerryPackageInfo::destroy($id);
    }

    public function update($id, array $data)
    {
        $post = KerryPackageInfo::find($id);

        if (!$post) {
            return false;
        }

        return $post->update($data);
    }
}