<?php

namespace App;

use App\Contracts\ScrapeItemInterface;
use App\Traits\ScrapeItemTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhotoGallery extends Model implements ScrapeItemInterface
{
    use ScrapeItemTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'number_photos'
    ];

    public function scrapeItem(): MorphOne
    {
        return $this->morphOne(ScrapeItem::class, 'scrapable');
    }

    public function height(): ?int
    {
        return null;
    }

    public function width(): ?int
    {
        return null;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function progress(): float
    {
        if (!$this->scrapeItem->log_path || !file_exists($this->scrapeItem->log_path)) {
            return 0;
        }

        $file = escapeshellarg($this->scrapeItem->log_path);
        $line = `tail -n 1 $file`;

        $progress = (int) $line;
        return (float) ($progress / $this->number_photos) * 100;
    }

    public function type(): string
    {
        return 'gallery';
    }

    public function fileExists(): bool
    {
        return $this->path() && is_dir($this->path());
    }

    public function removeFiles(): void
    {
        if ($this->fileExists()) {
            // delete all files in directory first
            array_map( 'unlink', array_filter((array) glob("{$this->path()}/*")));
            rmdir($this->path());
        }
    }
}
