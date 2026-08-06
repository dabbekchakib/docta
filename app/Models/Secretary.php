<?php

namespace App\Models;

use App\Enums\Governorate;
use App\Enums\SecretaryGender;
use App\Enums\SecretaryStatus;
use App\Observers\SecretaryObserver;
use Database\Factories\SecretaryFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([SecretaryObserver::class])]
class Secretary extends Model implements HasMedia
{
    /** @use HasFactory<SecretaryFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'user_id',
        'secretary_code',
        'photo',
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'cin',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'governorate',
        'postal_code',
        'employee_number',
        'hire_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'gender' => SecretaryGender::class,
            'status' => SecretaryStatus::class,
            'governorate' => Governorate::class,
            'birth_date' => 'date',
            'hire_date' => 'date',
        ];
    }

    public function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Journal d'activité.
     *
     * @return MorphMany<Activity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('documents')
            ->useDisk('public');
    }
}
