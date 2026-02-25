<?php
// app/View/Components/LocationPicker.php

namespace App\View\Components;

use Illuminate\View\Component;

class LocationPicker extends Component
{
    public string $latName;
    public string $lngName;
    public ?float $latValue;
    public ?float $lngValue;
    public string $label;
    public ?string $hint;
    public ?string $description;
    public bool $useCurrentLocationAsDefault;

    public function __construct(
        string $latName = 'latitude',
        string $lngName = 'longitude',
        ?float $latValue = null,
        ?float $lngValue = null,
        string $label = 'Location on Map',
        ?string $hint = null,
        ?string $description = null,
        bool $useCurrentLocationAsDefault = false
    ) {
        $this->latName      = $latName;
        $this->lngName      = $lngName;
        $this->latValue     = $latValue;
        $this->lngValue     = $lngValue;
        $this->label        = $label;
        $this->hint         = $hint;
        $this->description  = $description;
        $this->useCurrentLocationAsDefault = $useCurrentLocationAsDefault;
    }

    public function render()
    {
        return view('components.location-picker');
    }
}
