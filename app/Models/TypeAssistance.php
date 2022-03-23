<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class TypeAssistance
 * 
 * @property int $id
 * @property int $associations_id
 * @property string $nom
 * @property string $montant
 * @property int $max
 * @property int $max_cycle
 * @property string $description
 * @property string $type
 * 
 * @property \App\Models\Association $association
 *
 * @package App\Models
 */
class TypeAssistance extends Eloquent
{
	protected $table = 'type_assistance';
	public $timestamps = false;

	protected $casts = [
		'associations_id' => 'int',
		'max' => 'int',
		'max_cycle' => 'int'
	];

	protected $fillable = [
		'associations_id',
		'nom',
		'montant',
		'max',
		'max_cycle',
		'description',
		'type'
	];

	public function association()
	{
		return $this->belongsTo(\App\Models\Association::class, 'associations_id');
	}
}
