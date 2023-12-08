<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\City;
use Filament\Tables;
use App\Models\State;
use App\Models\Country;
use App\Models\Employee;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EmployeeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use Filament\Tables\Filters\SelectFilter;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
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
                        TextInput::make('first_name')->required(),
                        TextInput::make('last_name')->required(),
                        TextInput::make('address')->required(),
                        TextInput::make('zip_code'),
                        DatePicker::make('birth_date')->required(),
                        DatePicker::make('hired_date')->required(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->sortable()->searchable(),
                TextColumn::make('department.name')->sortable()->searchable(),
                TextColumn::make('hired_date')->date()->sortable(),
                TextColumn::make('created_at'),
            ])
            ->filters([
                SelectFilter::make('department')->relationship('department', 'name')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
