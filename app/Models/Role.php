<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Role
 * 
 * @property int $id
 * @property string $libelle
 * 
 * @property \Illuminate\Database\Eloquent\Collection $privileges
 *
 * @package App\Models
 */
class Role extends Eloquent
{
	public $timestamps = false;

	protected $fillable = [
		'libelle'
	];

	public function privileges()
	{
		return $this->hasMany(\App\Models\Privilege::class, 'roles_id');
	}
}
