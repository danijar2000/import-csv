<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use Illuminate\Database\QueryException;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import customers in files';

    protected $imports_folder = 'app/imports/';
    protected $results_folder = 'app/results/';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Start import');
        try {
            $files = scandir(storage_path($this->imports_folder));
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) !== 'csv') continue;
                $this->info('File: ' . $file);
                $this->import($file);
            }
        } catch (\Exception $e) {
            $this->error($e->getLine() . ': ' .$e->getMessage());
        }
        $this->info('Finish. Bye!');
        return 0;
    }

    /**
     * @param string $file
     * @return void
     */
    private function import(string $file): void
    {
        $country_names = array_flip($this->countries);
        $import_fp = fopen(storage_path($this->imports_folder) . $file, 'r');
        $result_fp = fopen(storage_path($this->results_folder) . $file, 'w');

        while (($line = fgetcsv($import_fp)) !== false) {
            if(!isset($first_row)) {
                $first_row = true;
                fputcsv($result_fp, array_merge($line, ['error']));
                continue;
            }

            if(isset($line[1])
                && preg_match('/([a-z]+)\s+([a-z]+)/i', $line[1], $matches)
                && isset($matches[1])
                && isset($matches[2])) {
                $name = $matches[1];
                $surname = $matches[2];
            } else {
                fputcsv($result_fp, array_merge($line, ['name']));
                continue;
            }

            if(isset($line[2]) && filter_var($line[2], FILTER_VALIDATE_EMAIL)) {
                $email = $line[2];
            } else {
                fputcsv($result_fp, array_merge($line, ['email']));
                continue;
            }

            if(!isset($line[3]) || !(preg_match('/(\d+)/', $line[3], $matches)
                    && isset($matches[1])
                    && ($age = (int)$matches[1])
                    && $age >= 18
                    && $age <= 99)) {
                fputcsv($result_fp, array_merge($line, ['age']));
                continue;
            }

            $location = isset($line[4]) ? trim($line[4]) !== '' ? trim($line[4]) : 'Unknown' : 'Unknown';
            $country_code = $location !== 'Unknown' ? $country_names[$location] ?? null : null;

            try {
                Customer::create([
                    'name' => $name,
                    'surname' => $surname,
                    'email' => $email,
                    'age' => $age,
                    'location' => $location,
                    'country_code' => $country_code,
                ]);
            } catch (QueryException $e) {
                //fputcsv($result_fp, array_merge($line, [$e->getMessage()]));
            }
        }
        fclose($import_fp);
        fclose($result_fp);
    }

    private $countries = [
        'ABW' => 'Aruba',
        'AFG' => 'Afghanistan',
        'AGO' => 'Angola',
        'AIA' => 'Anguilla',
        'ALA' => 'Åland Islands',
        'ALB' => 'Albania',
        'AND' => 'Andorra',
        'ARE' => 'United Arab Emirates',
        'ARG' => 'Argentina',
        'ARM' => 'Armenia',
        'ASM' => 'American Samoa',
        'ATA' => 'Antarctica',
        'ATF' => 'French Southern Territories',
        'ATG' => 'Antigua and Barbuda',
        'AUS' => 'Australia',
        'AUT' => 'Austria',
        'AZE' => 'Azerbaijan',
        'BDI' => 'Burundi',
        'BEL' => 'Belgium',
        'BEN' => 'Benin',
        'BES' => 'Bonaire, Sint Eustatius and Saba',
        'BFA' => 'Burkina Faso',
        'BGD' => 'Bangladesh',
        'BGR' => 'Bulgaria',
        'BHR' => 'Bahrain',
        'BHS' => 'Bahamas',
        'BIH' => 'Bosnia and Herzegovina',
        'BLM' => 'Saint Barthélemy',
        'BLR' => 'Belarus',
        'BLZ' => 'Belize',
        'BMU' => 'Bermuda',
        'BOL' => 'Bolivia, Plurinational State of',
        'BRA' => 'Brazil',
        'BRB' => 'Barbados',
        'BRN' => 'Brunei Darussalam',
        'BTN' => 'Bhutan',
        'BVT' => 'Bouvet Island',
        'BWA' => 'Botswana',
        'CAF' => 'Central African Republic',
        'CAN' => 'Canada',
        'CCK' => 'Cocos (Keeling) Islands',
        'CHE' => 'Switzerland',
        'CHL' => 'Chile',
        'CHN' => 'China',
        'CIV' => 'Côte d\'Ivoire',
        'CMR' => 'Cameroon',
        'COD' => 'Congo, the Democratic Republic of the',
        'COG' => 'Congo',
        'COK' => 'Cook Islands',
        'COL' => 'Colombia',
        'COM' => 'Comoros',
        'CPV' => 'Cape Verde',
        'CRI' => 'Costa Rica',
        'CUB' => 'Cuba',
        'CUW' => 'Curaçao',
        'CXR' => 'Christmas Island',
        'CYM' => 'Cayman Islands',
        'CYP' => 'Cyprus',
        'CZE' => 'Czech Republic',
        'DEU' => 'Germany',
        'DJI' => 'Djibouti',
        'DMA' => 'Dominica',
        'DNK' => 'Denmark',
        'DOM' => 'Dominican Republic',
        'DZA' => 'Algeria',
        'ECU' => 'Ecuador',
        'EGY' => 'Egypt',
        'ERI' => 'Eritrea',
        'ESH' => 'Western Sahara',
        'ESP' => 'Spain',
        'EST' => 'Estonia',
        'ETH' => 'Ethiopia',
        'FIN' => 'Finland',
        'FJI' => 'Fiji',
        'FLK' => 'Falkland Islands (Malvinas)',
        'FRA' => 'France',
        'FRO' => 'Faroe Islands',
        'FSM' => 'Micronesia, Federated States of',
        'GAB' => 'Gabon',
        'GBR' => 'United Kingdom',
        'GEO' => 'Georgia',
        'GGY' => 'Guernsey',
        'GHA' => 'Ghana',
        'GIB' => 'Gibraltar',
        'GIN' => 'Guinea',
        'GLP' => 'Guadeloupe',
        'GMB' => 'Gambia',
        'GNB' => 'Guinea-Bissau',
        'GNQ' => 'Equatorial Guinea',
        'GRC' => 'Greece',
        'GRD' => 'Grenada',
        'GRL' => 'Greenland',
        'GTM' => 'Guatemala',
        'GUF' => 'French Guiana',
        'GUM' => 'Guam',
        'GUY' => 'Guyana',
        'HKG' => 'Hong Kong',
        'HMD' => 'Heard Island and McDonald Islands',
        'HND' => 'Honduras',
        'HRV' => 'Croatia',
        'HTI' => 'Haiti',
        'HUN' => 'Hungary',
        'IDN' => 'Indonesia',
        'IMN' => 'Isle of Man',
        'IND' => 'India',
        'IOT' => 'British Indian Ocean Territory',
        'IRL' => 'Ireland',
        'IRN' => 'Iran, Islamic Republic of',
        'IRQ' => 'Iraq',
        'ISL' => 'Iceland',
        'ISR' => 'Israel',
        'ITA' => 'Italy',
        'JAM' => 'Jamaica',
        'JEY' => 'Jersey',
        'JOR' => 'Jordan',
        'JPN' => 'Japan',
        'KAZ' => 'Kazakhstan',
        'KEN' => 'Kenya',
        'KGZ' => 'Kyrgyzstan',
        'KHM' => 'Cambodia',
        'KIR' => 'Kiribati',
        'KNA' => 'Saint Kitts and Nevis',
        'KOR' => 'Korea, Republic of',
        'KWT' => 'Kuwait',
        'LAO' => 'Lao People\'s Democratic Republic',
        'LBN' => 'Lebanon',
        'LBR' => 'Liberia',
        'LBY' => 'Libya',
        'LCA' => 'Saint Lucia',
        'LIE' => 'Liechtenstein',
        'LKA' => 'Sri Lanka',
        'LSO' => 'Lesotho',
        'LTU' => 'Lithuania',
        'LUX' => 'Luxembourg',
        'LVA' => 'Latvia',
        'MAC' => 'Macao',
        'MAF' => 'Saint Martin (French part)',
        'MAR' => 'Morocco',
        'MCO' => 'Monaco',
        'MDA' => 'Moldova, Republic of',
        'MDG' => 'Madagascar',
        'MDV' => 'Maldives',
        'MEX' => 'Mexico',
        'MHL' => 'Marshall Islands',
        'MKD' => 'Macedonia, the former Yugoslav Republic of',
        'MLI' => 'Mali',
        'MLT' => 'Malta',
        'MMR' => 'Myanmar',
        'MNE' => 'Montenegro',
        'MNG' => 'Mongolia',
        'MNP' => 'Northern Mariana Islands',
        'MOZ' => 'Mozambique',
        'MRT' => 'Mauritania',
        'MSR' => 'Montserrat',
        'MTQ' => 'Martinique',
        'MUS' => 'Mauritius',
        'MWI' => 'Malawi',
        'MYS' => 'Malaysia',
        'MYT' => 'Mayotte',
        'NAM' => 'Namibia',
        'NCL' => 'New Caledonia',
        'NER' => 'Niger',
        'NFK' => 'Norfolk Island',
        'NGA' => 'Nigeria',
        'NIC' => 'Nicaragua',
        'NIU' => 'Niue',
        'NLD' => 'Netherlands',
        'NOR' => 'Norway',
        'NPL' => 'Nepal',
        'NRU' => 'Nauru',
        'NZL' => 'New Zealand',
        'OMN' => 'Oman',
        'PAK' => 'Pakistan',
        'PAN' => 'Panama',
        'PCN' => 'Pitcairn',
        'PER' => 'Peru',
        'PHL' => 'Philippines',
        'PLW' => 'Palau',
        'PNG' => 'Papua New Guinea',
        'POL' => 'Poland',
        'PRI' => 'Puerto Rico',
        'PRK' => 'Korea, Democratic People\'s Republic of',
        'PRT' => 'Portugal',
        'PRY' => 'Paraguay',
        'PSE' => 'Palestine, State of',
        'PYF' => 'French Polynesia',
        'QAT' => 'Qatar',
        'REU' => 'Réunion',
        'ROU' => 'Romania',
        'RUS' => 'Russian Federation',
        'RWA' => 'Rwanda',
        'SAU' => 'Saudi Arabia',
        'SDN' => 'Sudan',
        'SEN' => 'Senegal',
        'SGP' => 'Singapore',
        'SGS' => 'South Georgia and the South Sandwich Islands',
        'SHN' => 'Saint Helena, Ascension and Tristan da Cunha',
        'SJM' => 'Svalbard and Jan Mayen',
        'SLB' => 'Solomon Islands',
        'SLE' => 'Sierra Leone',
        'SLV' => 'El Salvador',
        'SMR' => 'San Marino',
        'SOM' => 'Somalia',
        'SPM' => 'Saint Pierre and Miquelon',
        'SRB' => 'Serbia',
        'SSD' => 'South Sudan',
        'STP' => 'Sao Tome and Principe',
        'SUR' => 'Suriname',
        'SVK' => 'Slovakia',
        'SVN' => 'Slovenia',
        'SWE' => 'Sweden',
        'SWZ' => 'Swaziland',
        'SXM' => 'Sint Maarten (Dutch part)',
        'SYC' => 'Seychelles',
        'SYR' => 'Syrian Arab Republic',
        'TCA' => 'Turks and Caicos Islands',
        'TCD' => 'Chad',
        'TGO' => 'Togo',
        'THA' => 'Thailand',
        'TJK' => 'Tajikistan',
        'TKL' => 'Tokelau',
        'TKM' => 'Turkmenistan',
        'TLS' => 'Timor-Leste',
        'TON' => 'Tonga',
        'TTO' => 'Trinidad and Tobago',
        'TUN' => 'Tunisia',
        'TUR' => 'Turkey',
        'TUV' => 'Tuvalu',
        'TWN' => 'Taiwan, Province of China',
        'TZA' => 'Tanzania, United Republic of',
        'UGA' => 'Uganda',
        'UKR' => 'Ukraine',
        'UMI' => 'United States Minor Outlying Islands',
        'URY' => 'Uruguay',
        'USA' => 'United States',
        'UZB' => 'Uzbekistan',
        'VAT' => 'Holy See (Vatican City State)',
        'VCT' => 'Saint Vincent and the Grenadines',
        'VEN' => 'Venezuela, Bolivarian Republic of',
        'VGB' => 'Virgin Islands, British',
        'VIR' => 'Virgin Islands, U.S.',
        'VNM' => 'Viet Nam',
        'VUT' => 'Vanuatu',
        'WLF' => 'Wallis and Futuna',
        'WSM' => 'Samoa',
        'YEM' => 'Yemen',
        'ZAF' => 'South Africa',
        'ZMB' => 'Zambia',
        'ZWE' => 'Zimbabwe'
    ];
}
