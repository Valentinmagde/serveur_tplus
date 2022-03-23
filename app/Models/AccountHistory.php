<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 14 Oct 2020 15:21:40 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AccountHistory
 * 
 * @property int $id
 * @property string $description
 * @property int $montant
 * @property int $created_at
 * @property int $updated_at
 * @property int $comptes_id
 * @property int $utilisateurs_id
 *
 * @package App\Models
 */
class AccountHistory extends Eloquent
{
	protected $casts = [
		'montant' => 'int',
		'created_at' => 'int',
		'updated_at' => 'int',
		'comptes_id' => 'int',
		'utilisateurs_id' => 'int'
	];

	protected $fillable = [
		'description',
		'montant',
		'comptes_id',
		'utilisateurs_id'
	];
}
