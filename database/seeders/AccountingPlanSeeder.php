<?php

namespace Database\Seeders;

use App\Accounting\AccountCode;
use App\Enums\AccountingAccountType;
use App\Models\AccountingAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountingPlanSeeder extends Seeder
{
    /**
     * Plan comptable simplifié (norme SCF tunisienne) adapté à un cabinet médical.
     *
     * @return array<int, array{code: string, name: string, type: AccountingAccountType, category: string, system?: bool}>
     */
    public static function accounts(): array
    {
        return [
            ['code' => '101', 'name' => 'Capital social', 'type' => AccountingAccountType::Equity, 'category' => 'ressources'],
            ['code' => '110', 'name' => 'Réserves légales', 'type' => AccountingAccountType::Equity, 'category' => 'ressources'],
            ['code' => '131', 'name' => 'Résultats reportés', 'type' => AccountingAccountType::Equity, 'category' => 'ressources'],
            ['code' => '164', 'name' => 'Emprunts et dettes financières', 'type' => AccountingAccountType::Liability, 'category' => 'ressources'],
            ['code' => '166', 'name' => 'Emprunts et dettes des associés', 'type' => AccountingAccountType::Liability, 'category' => 'ressources'],
            ['code' => '201', 'name' => 'Frais de constitution', 'type' => AccountingAccountType::Asset, 'category' => 'immobilisations'],
            ['code' => '206', 'name' => 'Logiciels', 'type' => AccountingAccountType::Asset, 'category' => 'immobilisations'],
            ['code' => '2181', 'name' => 'Matériel médical', 'type' => AccountingAccountType::Asset, 'category' => 'immobilisations'],
            ['code' => '2183', 'name' => 'Matériel de bureau et informatique', 'type' => AccountingAccountType::Asset, 'category' => 'immobilisations'],
            ['code' => '2218', 'name' => 'Amortissements — frais de constitution', 'type' => AccountingAccountType::Asset, 'category' => 'immobilisations'],
            ['code' => '2618', 'name' => 'Amortissements — matériel médical', 'type' => AccountingAccountType::Asset, 'category' => 'immobilisations'],
            ['code' => '30', 'name' => 'Stocks de marchandises', 'type' => AccountingAccountType::Asset, 'category' => 'stocks'],
            ['code' => '401', 'name' => 'Fournisseurs', 'type' => AccountingAccountType::Liability, 'category' => 'tiers'],
            ['code' => '4111', 'name' => 'Clients', 'type' => AccountingAccountType::Asset, 'category' => 'tiers', 'system' => true],
            ['code' => '4118', 'name' => 'Clients douteux', 'type' => AccountingAccountType::Asset, 'category' => 'tiers'],
            ['code' => '413', 'name' => 'Clients, effets à recevoir', 'type' => AccountingAccountType::Asset, 'category' => 'tiers'],
            ['code' => '421', 'name' => 'Personnel, rémunérations dues', 'type' => AccountingAccountType::Liability, 'category' => 'tiers'],
            ['code' => '425', 'name' => 'Personnel, avances et acomptes', 'type' => AccountingAccountType::Asset, 'category' => 'tiers'],
            ['code' => '431', 'name' => 'État, impôts sur les bénéfices', 'type' => AccountingAccountType::Liability, 'category' => 'tiers'],
            ['code' => '4351', 'name' => 'État, impôts et taxes', 'type' => AccountingAccountType::Liability, 'category' => 'tiers'],
            ['code' => '4441', 'name' => 'État, TVA collectée', 'type' => AccountingAccountType::Liability, 'category' => 'tiers', 'system' => true],
            ['code' => '4451', 'name' => 'État, TVA récupérable', 'type' => AccountingAccountType::Asset, 'category' => 'tiers'],
            ['code' => '451', 'name' => 'Associés, comptes courants', 'type' => AccountingAccountType::Liability, 'category' => 'tiers'],
            ['code' => '481', 'name' => 'Charges constatées d\'avance', 'type' => AccountingAccountType::Asset, 'category' => 'tiers'],
            ['code' => '486', 'name' => 'Produits constatés d\'avance', 'type' => AccountingAccountType::Liability, 'category' => 'tiers'],
            ['code' => AccountCode::BANK, 'name' => 'Banque', 'type' => AccountingAccountType::Asset, 'category' => 'financier', 'system' => true],
            ['code' => '521', 'name' => 'Comptes postaux (CCP)', 'type' => AccountingAccountType::Asset, 'category' => 'financier'],
            ['code' => AccountCode::CASH, 'name' => 'Caisse', 'type' => AccountingAccountType::Asset, 'category' => 'financier', 'system' => true],
            ['code' => '532', 'name' => 'Caisse en devises', 'type' => AccountingAccountType::Asset, 'category' => 'financier'],
            ['code' => '601', 'name' => 'Achats de marchandises', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '604', 'name' => 'Achats de fournitures', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '606', 'name' => 'Achats non stockés de matières et fournitures', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '613', 'name' => 'Sous-traitance générale', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '623', 'name' => 'Locations', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '625', 'name' => 'Assurances', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '628', 'name' => 'Entretien et réparations', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '633', 'name' => 'Transports', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '641', 'name' => 'Charges de personnel', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '645', 'name' => 'Charges sociales', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '651', 'name' => 'Charges financières', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '6592', 'name' => 'Pénalités fiscales', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => '802', 'name' => 'Dotations aux amortissements et provisions', 'type' => AccountingAccountType::Expense, 'category' => 'charges'],
            ['code' => AccountCode::REVENUE, 'name' => 'Prestations de services', 'type' => AccountingAccountType::Revenue, 'category' => 'produits', 'system' => true],
            ['code' => AccountCode::REVENUE_CONTRA, 'name' => 'Remises, rabais et ristournes accordés', 'type' => AccountingAccountType::Revenue, 'category' => 'produits', 'system' => true, 'balance' => 'debit'],
            ['code' => '751', 'name' => 'Produits financiers', 'type' => AccountingAccountType::Revenue, 'category' => 'produits'],
            ['code' => '771', 'name' => 'Produits exceptionnels', 'type' => AccountingAccountType::Revenue, 'category' => 'produits'],
        ];
    }

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (self::accounts() as $account) {
                $type = $account['type'];
                $normalBalance = $account['balance'] ?? $type->normalBalance();

                AccountingAccount::updateOrCreate(
                    ['code' => $account['code']],
                    [
                        'name' => $account['name'],
                        'type' => $type,
                        'category' => $account['category'],
                        'normal_balance' => $normalBalance,
                        'is_system' => $account['system'] ?? false,
                        'is_active' => true,
                        'deleted_at' => null,
                    ]
                );
            }
        });
    }
}
