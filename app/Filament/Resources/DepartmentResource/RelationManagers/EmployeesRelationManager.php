<?php

namespace App\Filament\Resources\DepartmentResource\RelationManagers;

use Filament\Forms;
use App\Models\City;
use Filament\Tables;
use App\Models\State;
use App\Models\Country;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make("Localization")->schema([
                    Select::make('country_id')
                        ->required()
                        ->label('Country')
                        ->options(Country::all()->pluck('name', 'id')->toArray())
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('state_id', null)),
                    Select::make('state_id')
                        ->required()
                        ->label('State')
                        ->options(function (callable $get) {
                            $country = Country::find($get('country_id'));
                            if (!$country) {
                                return State::all()->pluck('name', 'id');
                            }
                            return $country->states->pluck('name', 'id');
                        })->reactive(),
                    Select::make('city_id')
                        ->required()
                        ->label('City')
                        ->options(function (callable $get) {
                            $state = State::find($get('state_id'));
                            if (!$state) {
                                return City::all()->pluck('name', 'id');
                            }
                            return $state->cities->pluck('name', 'id');
                        }),
                    Select::make('department_id')
                        ->required()
                        ->label('Department')
                        ->options(Department::all()->pluck('name', 'id')),

                ])->columns(2),
                Section::make("Personal Informations")
                    ->schema([
                        TextInput::make('first_name')->required()->maxLength(255),
                        TextInput::make('last_name')->required()->maxLength(255),
                        TextInput::make('address')->required()->maxLength(255),
                        TextInput::make('zip_code')->maxLength(5),
                        DatePicker::make('birth_date')->required(),
                        DatePicker::make('hired_date')->required(),
                    ])
                    ->columns(2)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->sortable()->searchable(),
                TextColumn::make('department.name')->sortable()->searchable(),
                TextColumn::make('hired_date')->date()->sortable(),
                TextColumn::make('created_at'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
