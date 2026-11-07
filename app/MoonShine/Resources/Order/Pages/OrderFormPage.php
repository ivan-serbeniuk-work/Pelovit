<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Order\Pages;

use App\MoonShine\Resources\Order\OrderResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Throwable;

/**
 * @extends FormPage<OrderResource>
 */
class OrderFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make('Статус', [
                Select::make('Статус', 'status')->options([
                    'pending' => 'Новий',
                    'confirmed' => 'Підтверджено',
                    'shipped' => 'Відправлено',
                    'completed' => 'Виконано',
                    'cancelled' => 'Скасовано',
                ]),
                Select::make('Оплата', 'payment_status')->options([
                    'pending' => 'Очікує',
                    'paid' => 'Оплачено',
                    'failed' => 'Не вдалась',
                ]),
            ]),
            Box::make('Склад замовлення', [
                Preview::make('Товари', 'items', function ($value) {
                    $items = is_array($value) ? $value : (json_decode($value ?? '[]', true) ?? []);
                    if (empty($items)) {
                        return '<p class="text-muted">Немає товарів</p>';
                    }
                    $rows = '';
                    $subtotal = 0;
                    foreach ($items as $item) {
                        $name = htmlspecialchars($item['name'] ?? '—');
                        $qty = (int) ($item['qty'] ?? 1);
                        $price = (float) ($item['price'] ?? 0);
                        $sum = $qty * $price;
                        $subtotal += $sum;
                        $rows .= "<tr>
                            <td>{$name}</td>
                            <td class='text-center'>{$qty}</td>
                            <td class='text-end'>".number_format($price, 2)." ₴</td>
                            <td class='text-end'><strong>".number_format($sum, 2).' ₴</strong></td>
                        </tr>';
                    }

                    return "<table class='table table-sm table-bordered mb-0'>
                        <thead class='table-light'>
                            <tr>
                                <th>Товар</th>
                                <th class='text-center' style='width:60px'>Кількість</th>
                                <th class='text-end' style='width:110px'>Ціна</th>
                                <th class='text-end' style='width:120px'>Сума</th>
                            </tr>
                        </thead>
                        <tbody>{$rows}</tbody>
                        <tfoot>
                            <tr>
                                <td colspan='3' class='text-end fw-semibold'>Разом:</td>
                                <td class='text-end fw-bold'>".number_format($subtotal, 2).' ₴</td>
                            </tr>
                        </tfoot>
                    </table>';
                }),
            ]),
            Box::make('Клієнт', [
                Grid::make([
                    Column::make([
                        Text::make("Ім'я", 'first_name')->readonly(),
                        Text::make('Прізвище', 'last_name')->readonly(),
                        Phone::make('Телефон', 'phone')->readonly(),
                        Email::make('Email', 'email')->readonly(),
                    ])->columnSpan(6),
                    Column::make([
                        Text::make('Місто', 'city')->readonly(),
                        Text::make('Відділення', 'branch')->readonly(),
                        Select::make('Спосіб оплати', 'payment_method')->options([
                            'card' => 'Карта (реквізити)',
                            'cod' => 'Накладний платіж',
                            'liqpay' => 'Карта онлайн (LiqPay)',
                        ])->readonly(),
                        Number::make('Сума', 'total')->readonly(),
                        Text::make('ID платежу LiqPay', 'payment_id')->readonly(),
                    ])->columnSpan(6),
                ]),
                Textarea::make('Коментар', 'comment')->readonly(),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [];
    }

    /**
     * @param  FormBuilder  $component
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
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
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
