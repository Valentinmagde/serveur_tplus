<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TypeSanction
 * 
 * @property int $id
 * @property int $associations_id
 * @property string $nom
 * @property float $montant
 * @property string $description
 * 
 * @property \App\Models\Association $association
 *
 * @package App\Models
 */
class TypeSanction extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'associations_id' => 'int',
		'montant' => 'float'
	];

	protected $fillable = [
		'associations_id',
		'nom',
		'montant',
		'description'
	];

	public function association()
	{
		return $this->belongsTo(\App\Models\Association::class, 'associations_id');
	}
}
