<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Lead\Pages;

use App\Models\Lead;
use App\MoonShine\Resources\Lead\LeadResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use Throwable;

/**
 * @extends IndexPage<LeadResource>
 */
class LeadIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [];
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }

    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [...parent::topLayer()];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [FlexibleRender::make($this->buildKanban())];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [...parent::bottomLayer()];
    }

    private function buildKanban(): string
    {
        $leads = Lead::orderBy('created_at', 'desc')->get()->groupBy('status');

        $statuses = [
            'new' => ['label' => 'Нові',      'color' => '#3b82f6'],
            'in_progress' => ['label' => 'В роботі',  'color' => '#f59e0b'],
            'done' => ['label' => 'Завершено', 'color' => '#10b981'],
        ];

        $methodLabels = ['call' => 'Дзвінок', 'telegram' => 'Telegram', 'viber' => 'Viber', 'whatsapp' => 'WhatsApp'];
        $sourceLabels = ['home' => 'Головна', 'contacts' => 'Контакти', 'masters' => 'Майстрам', 'opt' => 'Опт', 'contract' => 'Контрактне', 'contract-calculator' => 'Контрактне (калькулятор)', 'contract-tester' => 'Контрактне (тестер)'];

        $csrf = csrf_token();

        $html = "<script src='https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js'></script>
        <style>
            .kanban-wrap{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;padding:4px 0 24px}
            .kanban-col{background:var(--color-body);border:1px solid var(--color-base-stroke);border-radius:12px;overflow:hidden;display:flex;flex-direction:column}
            .kanban-head{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--color-base-stroke);flex-shrink:0}
            .kanban-head-title{font-size:13px;font-weight:700;letter-spacing:.03em;display:flex;align-items:center;gap:8px}
            .kanban-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
            .kanban-count{font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;color:#fff;opacity:.85}
            .kanban-body{padding:10px;display:flex;flex-direction:column;gap:8px;min-height:120px;flex:1}
            .kanban-card{background:var(--color-body);border:1px solid var(--color-base-stroke);border-radius:8px;padding:12px 14px;font-size:13px;cursor:grab;user-select:none;transition:box-shadow .15s,transform .15s}
            .kanban-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08)}
            .kanban-card.sortable-ghost{opacity:.35;transform:rotate(1.5deg)}
            .kanban-card.sortable-drag{box-shadow:0 8px 24px rgba(0,0,0,.15);cursor:grabbing}
            .kanban-card-name{font-weight:600;margin-bottom:4px;color:var(--color-base-text)}
            .kanban-card-phone{color:var(--color-base-text);opacity:.7;margin-bottom:6px;font-size:12px}
            .kanban-card-badges{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
            .kanban-badge{font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px;background:var(--color-primary-opacity);color:var(--color-primary)}
            .kanban-card-date{font-size:10px;color:var(--color-base-text);opacity:.4}
            .kanban-empty{font-size:12px;color:var(--color-base-text);opacity:.3;text-align:center;padding:24px 0}
            .kanban-col--over .kanban-body{background:rgba(99,102,241,.04);border-radius:0 0 12px 12px}
            .kanban-saving{pointer-events:none;opacity:.6}
            @media(max-width:900px){.kanban-wrap{grid-template-columns:1fr}}
        </style>
        <div class='kanban-wrap'>";

        foreach ($statuses as $key => $meta) {
            $colLeads = $leads->get($key, collect());
            $count = $colLeads->count();
            $color = $meta['color'];

            $html .= "<div class='kanban-col'>
                <div class='kanban-head'>
                    <div class='kanban-head-title'>
                        <span class='kanban-dot' style='background:{$color}'></span>
                        {$meta['label']}
                    </div>
                    <span class='kanban-count' id='count-{$key}' style='background:{$color}'>{$count}</span>
                </div>
                <div class='kanban-body' data-status='{$key}'>";

            if ($colLeads->isEmpty()) {
                $html .= "<div class='kanban-empty'>Перетягніть сюди</div>";
            }

            foreach ($colLeads as $lead) {
                $method = $methodLabels[$lead->contact_method] ?? $lead->contact_method;
                $source = $sourceLabels[$lead->source] ?? $lead->source;
                $date = $lead->created_at?->format('d.m.Y H:i') ?? '';
                $company = $lead->company ? "<span class='kanban-badge'>".e($lead->company).'</span>' : '';

                $html .= "<div class='kanban-card' data-id='{$lead->id}' data-status='{$key}'>
                    <div class='kanban-card-name'>".e($lead->name)."</div>
                    <div class='kanban-card-phone'>".e($lead->phone)."</div>
                    <div class='kanban-card-badges'>
                        <span class='kanban-badge'>{$method}</span>
                        <span class='kanban-badge'>{$source}</span>
                        {$company}
                    </div>
                    <div class='kanban-card-date'>{$date}</div>
                </div>";
            }

            $html .= '</div></div>';
        }

        $html .= "</div>
        <script>
        (function() {
            const csrf = '{$csrf}';

            function updateCount(status) {
                const col = document.querySelector('[data-status=\"' + status + '\"]');
                const count = col.querySelectorAll('.kanban-card').length;
                const badge = document.getElementById('count-' + status);
                if (badge) badge.textContent = count;

                const empty = col.querySelector('.kanban-empty');
                if (count === 0 && !empty) {
                    const el = document.createElement('div');
                    el.className = 'kanban-empty';
                    el.textContent = 'Перетягніть сюди';
                    col.appendChild(el);
                } else if (count > 0 && empty) {
                    empty.remove();
                }
            }

            function saveStatus(id, status, card) {
                card.classList.add('kanban-saving');
                fetch('/admin/leads/' + id + '/status', {
                    method: 'PATCH',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf},
                    body: JSON.stringify({status: status})
                })
                .then(r => { if (!r.ok) throw new Error(); })
                .catch(() => { location.reload(); })
                .finally(() => card.classList.remove('kanban-saving'));
            }

            document.querySelectorAll('.kanban-body').forEach(function(col) {
                Sortable.create(col, {
                    group: 'kanban',
                    animation: 180,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        const card      = evt.item;
                        const newStatus = evt.to.dataset.status;
                        const oldStatus = evt.from.dataset.status;
                        if (newStatus === oldStatus) return;
                        card.dataset.status = newStatus;
                        updateCount(oldStatus);
                        updateCount(newStatus);
                        saveStatus(card.dataset.id, newStatus, card);
                    }
                });
            });
        })();
        </script>";

        return $html;
    }
}
