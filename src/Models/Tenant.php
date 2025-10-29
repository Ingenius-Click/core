<?php

namespace Ingenius\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as ModelsTenant;

class Tenant extends ModelsTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'template_id',
        'styles',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'styles' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function domains(): HasMany {
        return $this->hasMany(Domain::class);
    }

    public function hasFeature(string $feature): bool
    {
        $features = $this->template->features;
        return in_array($feature, $features);
    }

    public function hasFeatures(array $features): bool
    {
        $templateFeatures = $this->template->features;
        return count(array_intersect($features, $templateFeatures)) === count($features);
    }

    /**
     * Get the name of the tenant.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->data['name'] ?? $this->id;
    }

    /**
     * Set the name of the tenant.
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $data = $this->data ?? [];
        $data['name'] = $name;
        $this->data = $data;
        $this->save();
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'template_id',
            'styles',
        ];
    }
}
