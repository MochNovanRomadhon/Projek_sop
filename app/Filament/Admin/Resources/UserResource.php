<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Direktorat;
use App\Models\Unit; 
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Collection;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1; 
    protected static ?string $navigationLabel = 'Manajemen Pengguna';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manajemen User';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi User')
                    ->schema([
                        // 1. ROLE DROPDOWN
                        Forms\Components\Select::make('roles')
                            ->label('Role Akun')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->live() 
                            ->afterStateUpdated(function (Set $set) {
                                $set('unit_id', null);
                                $set('direktorat_id', null);
                            }),

                        // 2. PILIH DIREKTORAT (Hanya jika Role = Pengusul)
                        Forms\Components\Select::make('direktorat_id')
                            ->label('Asal Direktorat')
                            ->options(Direktorat::query()->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('unit_id', null))
                            ->visible(function (Get $get) {
                                $roleIds = $get('roles');
                                $roleId = is_array($roleIds) ? ($roleIds[0] ?? null) : $roleIds;
                                if (!$roleId) return false;
                                
                                $role = Role::find($roleId);
                                return $role && strtolower($role->name) === 'pengusul'; 
                            })
                            ->dehydrated(false), 

                        // 3. PILIH UNIT KERJA (Hanya jika Role = Pengusul)
                        Forms\Components\Select::make('unit_id')
                            ->label('Unit Kerja')
                            ->options(function (Get $get): Collection {
                                $direktoratId = $get('direktorat_id');
                                if (!$direktoratId) {
                                    return collect([]);
                                }
                                return Unit::query()
                                    ->where('direktorat_id', $direktoratId)
                                    ->pluck('nama', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->visible(function (Get $get) {
                                $roleIds = $get('roles');
                                $roleId = is_array($roleIds) ? ($roleIds[0] ?? null) : $roleIds;
                                if (!$roleId) return false;
                                
                                $role = Role::find($roleId);
                                return $role && strtolower($role->name) === 'pengusul';
                            })
                            ->required(function (Get $get) {
                                $roleIds = $get('roles');
                                $roleId = is_array($roleIds) ? ($roleIds[0] ?? null) : $roleIds;
                                if (!$roleId) return false;
                                
                                $role = Role::find($roleId);
                                return $role && strtolower($role->name) === 'pengusul';
                            }),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->label('Nama Lengkap'),
                            
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(150)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('telp')
                            ->tel()
                            ->maxLength(13)
                            ->label('Nomor Telepon'),

                        // 4. PASSWORD DENGAN IKON MATA
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable() // [BARU] Ini yang memunculkan ikon mata
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->label('Password'),
                        
                        // [TOGGLE DI FORM] Tetap ada agar bisa edit status
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Akun (Aktif)')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true), 

                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label('Nama Lengkap'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'admin' => 'danger',
                        'verifikator' => 'warning',
                        'pengusul' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit.nama')
                    ->label('Unit Kerja')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),

                // [PERBAIKAN] Mengubah ToggleColumn menjadi TextColumn dengan Badge
                // Ini mencegah error 403 karena tabel sekarang Read-Only
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Non Aktif')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter Role')
                    ->relationship('roles', 'name'),
                    
                Tables\Filters\SelectFilter::make('unit_id')
                    ->relationship('unit', 'nama')
                    ->label('Filter Unit')
                    ->searchable()
                    ->preload(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}