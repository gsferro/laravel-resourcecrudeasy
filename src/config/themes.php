<?php

return [
    'default'   => 'bootstrap', // tema padrão
    'framework' => [
        'bootstrap' => [
            'form' => [
                'group'    => 'form-group',
                'label'    => 'form-label',
                'input'    => 'form-control',
                'error'    => 'is-invalid',
                'help'     => 'form-text text-muted',
                'textarea' => 'form-control',
                'select'   => 'form-control',
                'button'   => 'btn',
            ],
            'link' => 'btn btn-link',
            'span' => '',
            'div'  => '',
            'img'  => 'img-fluid'
        ],
        'material'  => [
            'form' => [
                'group'    => 'mdc-text-field',
                'label'    => '',
                'input'    => 'mdc-text-field__input',
                'error'    => 'mdc-text-field--invalid',
                'help'     => 'mdc-text-field-helptext',
                'textarea' => 'mdc-text-field__input',
                'select'   => 'mdc-select__native-control',
                'button'   => 'mdc-button',
            ],
            'link' => 'mdc-button mdc-button--unelevated',
            'span' => '',
            'div'  => '',
            'img'  => 'mdc-image-list__image'
        ],
        'tailwind'  => [
            'form' => [
                'group'    => 'mdc-text-field',
                'label'    => '',
                'input'    => 'mdc-text-field__input',
                'error'    => 'mdc-text-field--invalid',
                'help'     => 'mdc-text-field-helptext',
                'textarea' => 'mdc-text-field__input',
                'select'   => 'mdc-select__native-control',
                'button'   => 'mdc-button',
            ],
            'link' => 'mdc-button mdc-button--unelevated',
            'span' => '',
            'div'  => '',
            'img'  => 'mdc-image-list__image'
        ],
        'dsgov'     => [
            'form' => [
                'group'    => 'br-input ',
                'label'    => 'govuk-label',
                'input'    => 'govuk-input',
                'textarea' => 'govuk-textarea',
                'select'   => 'govuk-select',
                'button'   => 'govuk-button',
            ],
            'link' => 'mdc-button mdc-button--unelevated',
            'span' => '',
            'div'  => '',
            'img'  => 'mdc-image-list__image'
        ],
    ]
];
