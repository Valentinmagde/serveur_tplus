<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:15 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class CategoriesNotification
 * 
 * @property int $id
 * @property string $libelle
 *
 * @package App\Models
 */
class CategoriesNotification extends Eloquent
{
	public $timestamps = false;

	protected $fillable = [
		'libelle'
	];
}
