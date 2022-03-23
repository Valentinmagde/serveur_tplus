<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Document
 * 
 * @property int $id
 * @property int $associations_id
 * @property int $create_at
 * @property int $update_at
 * @property string $intitule
 * @property string $path
 * @property string $description
 * 
 * @property \App\Models\Association $association
 *
 * @package App\Models
 */
class Document extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'associations_id' => 'int',
		'create_at' => 'int',
		'update_at' => 'int'
	];

	protected $fillable = [
		'associations_id',
		'create_at',
		'update_at',
		'intitule',
		'path',
		'description'
	];

	public function association()
	{
		return $this->belongsTo(\App\Models\Association::class, 'associations_id');
	}
}
