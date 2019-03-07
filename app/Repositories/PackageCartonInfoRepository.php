<?php
/**
 * Created by PhpStorm.
 * User: bryan.yen
 * Date: 2018/11/15
 * Time: 11:41 AM
 */

namespace App\Repositories;


use App\Entities\KerryPackageCartonInfo;

class PackageCartonInfoRepository
{
    public function index()
    {
        return KerryPackageCartonInfo::get();
    }

    public function create(array $data)
    {
        return KerryPackageCartonInfo::create($data);
    }

    public function find($id)
    {
        return KerryPackageCartonInfo::find($id);
    }

    public function delete($id)
    {
        return KerryPackageCartonInfo::destroy($id);
    }

    public function update($id, array $data)
    {
        $post = KerryPackageCartonInfo::find($id);

        if (!$post) {
            return false;
        }

        return $post->update($data);
    }
}