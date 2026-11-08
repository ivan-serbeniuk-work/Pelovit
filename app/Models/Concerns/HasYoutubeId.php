<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/**
 * Витягує ID ролика з колонки video_url. Підмішується в моделі, де є
 * YouTube-посилання (товар, стаття): регекс один і той самий, дублювати
 * його в двох моделях — гарантовано розійтися при наступній правці.
 */
trait HasYoutubeId
{
    /** ID ролика з будь-якого формату посилання: watch?v=, youtu.be/, shorts/, embed/, live/. */
    public function getYoutubeIdAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([\w-]{11})~', $this->video_url, $m);

        return $m[1] ?? null;
    }
}
