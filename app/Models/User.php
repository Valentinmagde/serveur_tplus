<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class User
 * 
 * @property string $uniqueId
 * @property string $username
 * @property string $hashedPassword
 * @property string $firstName
 * @property string $lastName
 * @property int $role
 * @property string $roleTitle
 * @property int $date_creation
 *
 * @package App\Models
 */
class User extends Eloquent
{
	protected $primaryKey = 'uniqueId';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'role' => 'int',
		'date_creation' => 'int'
	];

	protected $fillable = [
		'username',
		'hashedPassword',
		'firstName',
		'lastName',
		'role',
		'roleTitle',
		'date_creation'
	];
}
