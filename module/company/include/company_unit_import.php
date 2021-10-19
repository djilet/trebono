<?php

class CompanyUnitImport extends LocalObject
{
    private $module;

    private $values;
    private $valueReceiptOption;
    private $dictionary;
    private $parentDictionary;
    private $productList;
    private $optionListSwitch;
    private $voucherScenario;
    private $voucherCategory;
    private $voucherScenarioList;
    private $voucherCategoryList;

    /**
     * Insert or update log message in import_company_unit_history
     *
     * @param string $content log message
     * @param string $type type of message to simplify search
     * @param int $importID import_id
     */
    public static function WriteLog($content, $type, $importID = false, $ended = false)
    {
        $stmt = GetStatement(DB_CONTROL);

        $user = new User();
        $user->LoadBySession();
        $userID = intval($user->GetProperty("user_id"));

        if (!$importID) {
            $query = "INSERT INTO import_company_unit_history(updated, content, created, user_id, ended)
                    VALUES(" . Connection::GetSQLString(GetCurrentDateTime()) . ", " . Connection::GetSQLString(date("d.m.Y H:i:s") . " [" . $type . "] " . $content) . ", " . Connection::GetSQLString(GetCurrentDateTime()) . ", " . $userID . ", 'N')
                RETURNING import_id";
        } else {
            $query = "UPDATE import_company_unit_history SET 
                        " . ($ended ? "ended = 'Y'," : "") . "
                        updated = " . Connection::GetSQLString(GetCurrentDateTime()) . ", 
                        content = import_company_unit_history.content || " . Connection::GetSQLString("\n" . date("d.m.Y H:i:s") . " [" . $type . "] " . $content) . "                      
                    WHERE import_id=" . intval($importID);
        }


        if (!$stmt->Execute($query)) {
            return false;
        }

        if (!$importID) {
            $importID = $stmt->GetLastInsertID();
        }

        return $importID;
    }

    /**
     * Set company_unit_id to import_company_unit_history
     *
     * @param int $importID import_id
     * @param int $companyUnitID company_unit_id
     */
    public static function SetCompanyUnitToHistory($importID, $companyUnitID)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "UPDATE import_company_unit_history SET
                    company_unit_id = " . intval($companyUnitID) . ",
                    updated = " . Connection::GetSQLString(GetCurrentDateTime()) . "
                WHERE import_id=" . intval($importID);

        $stmt->Execute($query);
    }

    /**
     * Set company_unit_id to import_company_unit_history
     *
     * @param int $importID import_id
     */
    public static function IncrementEmployeeCounterToHistory($importID)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "UPDATE import_company_unit_history SET
                    employee_count = employee_count + 1,
                    updated = " . Connection::GetSQLString(GetCurrentDateTime()) . "
                WHERE import_id=" . intval($importID);

        $stmt->Execute($query);
    }

    /**
     * Constructor
     *
     * @param string $module Name of context module
     */
    public function CompanyUnitImport($module)
    {
        $this->module = $module;
        $this->initDictionary();
    }

    public function SetStartDate($cells, &$startDate)
    {
        for ($i = 0; $i < count($cells); $i++) {
            for ($j = 0; $j < count($cells[$i]) - 1; $j++) {
                if ($cells[$i][$j] !== "Gewünschtes Startdatum") {
                    continue;
                }

                if (!isset($cells[$i][$j + 1])) {
                    continue;
                }

                $date = date_create_from_format("d/m/Y", $cells[$i][$j + 1]);
                if ($date !== false) {
                    $startDate = $date->format("Y-m-d");
                } elseif ($startTime = strtotime($cells[$i][$j + 1])) {
                    $startDate = date("Y-m-d", $startTime);
                }
            }
        }
    }

    /**
     * first of all define where contact person block starts and ends
     */
    private function SetContactCellNumbers($cells, &$cellNumberContactStart, &$cellNumberContactEnd)
    {
        for ($i = 0; $i < count($cells); $i++) {
            if (empty($cellNumberContactStart) && isset($cells[$i][0]) && $cells[$i][0] == "Ansprechpartner") {
                $cellNumberContactStart = $i + 1;
            }
            if (!empty($cellNumberContactEnd) || !isset($cells[$i][0]) || $cells[$i][0] != "Bank Details") {
                continue;
            }

            $cellNumberContactEnd = $i - 2;
        }
    }

    /**
     * extract company unit fields formatted like key on the left and value on the right. also take services start date
     */
    public function SetCompanyUnitData($cells, &$dataCompanyUnit, &$parentCompanyUnitData, &$startDate)
    {
        $this->SetContactCellNumbers($cells, $cellNumberContactStart, $cellNumberContactEnd);

        for ($i = 0; $i < count($cells); $i++) {
            for ($j = 0; $j < count($cells[$i]) - 1; $j++) {
                if (isset($this->dictionary[$cells[$i][$j]]) && ($i < $cellNumberContactStart - 1 || $i > $cellNumberContactEnd)) {
                    if ($cells[$i][$j] != "KD.Nr. trebono Cloud Service" || $cells[$i - 1][$j] == "Unternehmensname:") {
                        $dataCompanyUnit[$this->dictionary[$cells[$i][$j]]] = trim($cells[$i][$j + 1]);
                    }
                }
                if (isset($this->parentDictionary[$cells[$i][$j]]) && ($i < $cellNumberContactStart - 1 || $i > $cellNumberContactEnd)) {
                    if ($cells[$i][$j] != "KD.Nr. trebono Cloud Service" || $cells[$i - 1][$j] == "Name \nMutter Organisation:") {
                        $parentCompanyUnitData[$this->parentDictionary[$cells[$i][$j]]] = trim($cells[$i][$j + 1]);
                    }
                }
                if ($cells[$i][$j] == "Gewünschtes Startdatum:" && empty($startDate)) {
                    $startDate = date("Y-m-d", strtotime($cells[$i][$j + 1]));
                }

                if ($cells[$i][$j] == "Unternehmens Standard für Gutschein Szenario's:") {
                    if (isset($this->voucherCategory["Scenario " . $cells[$i + 1][$j]])) {
                        $dataCompanyUnit['OptionList'][] = [
                            "option_id" => Option::GetOptionIDByCode(
                                $this->voucherCategory["Scenario " . $cells[$i + 1][$j]]
                            ),
                            "value" => $this->voucherScenario[
                                $this->voucherScenarioList[$cells[$i + 1][$j + 1]]
                            ],
                        ];
                    }
                    if (isset($this->voucherCategory["Scenario " . $cells[$i + 2][$j]])) {
                        $dataCompanyUnit['OptionList'][] = [
                            "option_id" => Option::GetOptionIDByCode(
                                $this->voucherCategory["Scenario " . $cells[$i + 2][$j]]
                            ),
                            "value" => $this->voucherScenario[
                            $this->voucherScenarioList[$cells[$i + 2][$j + 1]]
                            ],
                        ];
                    }
                } elseif ($cells[$i][$j] == "Unternehmens Standard für Gutschein Kategorien:") {
                    if (isset($this->voucherCategory["Category " . $cells[$i + 1][$j]])) {
                        $dataCompanyUnit['OptionList'][] = [
                            "option_id" => Option::GetOptionIDByCode(
                                $this->voucherCategory["Category " . $cells[$i + 1][$j]]
                            ),
                            "value" => $this->voucherCategoryList[$cells[$i + 1][$j + 1]],
                        ];
                    }
                    if (isset($this->voucherCategory["Category " . $cells[$i + 2][$j]])) {
                        $dataCompanyUnit['OptionList'][] = [
                            "option_id" => Option::GetOptionIDByCode(
                                $this->voucherCategory["Category " . $cells[$i + 2][$j]]
                            ),
                            "value" => $this->voucherCategoryList[$cells[$i + 2][$j + 1]],
                        ];
                    }
                }

                if (in_array($this->values[trim($cells[$i][$j])], ["last_month", "current_month"])) {
                    $dataCompanyUnit["payroll_month"] = $this->values[trim($cells[$i][$j])];
                }

                if ($cells[$i][$j] == "Buchhaltungskonten") {
                    break 2;
                }
            }
        }

        $this->SetAdditionalCompanyUnitData($cells, $dataCompanyUnit);

        if (empty($startDate)) {
            $startDate = date("Y-m-d");
        }

        $dataCompanyUnit['ContractList'][] = array(
            "product_id" => Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
            "start_date" => $startDate
        );

        $dataCompanyUnit["colorscheme"] = '';
        $dataCompanyUnit['agreement_enable'] = 'N';
    }

    private function SetAdditionalCompanyUnitData($cells, &$dataCompanyUnit)
    {
        for ($i = 0; $i < count($cells); $i++) {
            if ($cells[$i][0] == "Buchhaltungskonten" || $i >= 3 && $cells[$i - 3][0] == "Buchhaltungskonten") {
                for ($j = 0; $j < count($cells[$i]); $j++) {
                    if (!isset($this->dictionary[$cells[$i + 1][$j]])) {
                        continue;
                    }

                    $dataCompanyUnit[$this->dictionary[$cells[$i + 1][$j]]] = $cells[$i + 2][$j];
                }
            } elseif ($cells[$i][0] == "Lohnabrechner ID (z.B. Datev)\nin der Lohnabrechnungssoftware") {
                if (isset($this->dictionary[$cells[$i][0]])) {
                    $dataCompanyUnit[$this->dictionary[$cells[$i][0]]] = $cells[$i][1];
                }
            }

            if ($cells[$i][0] == "Basis SEPA Nummer") {
                if (isset($cells[$i + 1][0])) {
                    $dataCompanyUnit[$this->dictionary[$cells[$i][0]]] = $cells[$i + 1][0];
                }
                if ($cells[$i + 3][0] == "Unterschrift Datum" && isset($cells[$i + 4][0])) {
                    $date = date_create_from_format("d/m/y", $cells[$i + 4][0]);
                    if ($date !== false) {
                        $dataCompanyUnit[$this->dictionary[$cells[$i][0]] . $this->dictionary[$cells[$i + 3][0]]] = $date->format("Y-m-d");
                    }
                }
            }
            if ($cells[$i][1] != "Firmen SEPA Nummer" || $cells[$i][3] != "Ind. Text Registrierung E-Mail") {
                continue;
            }

            if (isset($cells[$i + 1][1])) {
                $dataCompanyUnit[$this->dictionary[$cells[$i][1]]] = $cells[$i + 1][1];
            }
            if (isset($cells[$i + 1][3])) {
                $dataCompanyUnit[$this->dictionary[$cells[$i][3]]] = $cells[$i + 1][3];
            }

            if ($cells[$i + 3][1] != "Unterschrift Datum" || !isset($cells[$i + 4][1])) {
                continue;
            }

            $date = date_create_from_format("d/m/y", $cells[$i + 4][1]);
            if ($date === false) {
                continue;
            }

            $dataCompanyUnit[$this->dictionary[$cells[$i][1]] . $this->dictionary[$cells[$i + 3][1]]] = $date->format("Y-m-d");
        }
    }

    public function SetContactData($cells, &$countContacts, &$dataContact)
    {
        $this->SetContactCellNumbers($cells, $cellNumberContactStart, $cellNumberContactEnd);

        for ($i = $cellNumberContactStart + 1; $i <= $cellNumberContactEnd; $i++) {
            for ($j = 0; $j < count($cells[$i]); $j++) {
                if ($cells[$cellNumberContactStart][$j] == "Verantwortlich für") {
                    $contactFor = explode(", ", $cells[$i][$j]);

                    $dataContact[$countContacts]["contact_for_company_unit_admin"] =
                        in_array("1 Unternehmen Admin", $contactFor)
                        || in_array("Beide Admin Rollen (1,2)", $contactFor)
                        || in_array("Alle Rollen (1 -7)", $contactFor)
                        || in_array("Alle Rollen mit Zugriff (1 - 4)", $contactFor)
                            ? "Y"
                            : "N";
                    $dataContact[$countContacts]["contact_for_employee_admin"] =
                        in_array("2 Mitarbeiter Admin", $contactFor)
                        || in_array("Beide Admin Rollen (1,2)", $contactFor)
                        || in_array("Alle Rollen (1 -7)", $contactFor)
                        || in_array("Alle Rollen mit Zugriff (1 - 4)", $contactFor)
                            ? "Y"
                            : "N";
                    $dataContact[$countContacts]["contact_for_contract"] =
                        in_array("7 Vertragsangelegenheiten", $contactFor)
                        || in_array("Alle Rollen (1 -7)", $contactFor)
                            ? "Y"
                            : "N";
                    $dataContact[$countContacts]["contact_for_invoice"] =
                        in_array("3 E-Rechnungsempfänger", $contactFor)
                        || in_array("Alle Rollen (1 -7)", $contactFor)
                        || in_array("Alle Rollen mit Zugriff (1 - 4)", $contactFor)
                            ? "Y"
                            : "N";
                    $dataContact[$countContacts]["contact_for_payroll_export"] =
                        in_array("4 Lohn Export Empfänger", $contactFor)
                        || in_array("Alle Rollen (1 -7)", $contactFor)
                        || in_array("Alle Rollen mit Zugriff (1 - 4)", $contactFor)
                            ? "Y"
                            : "N";
                    $dataContact[$countContacts]["contact_for_service"] =
                        in_array("Services - Super User", $contactFor)
                        || in_array("6 ADV Vertrag", $contactFor)
                        || in_array("Alle Rollen (1 -7)", $contactFor)
                            ? "Y"
                            : "N";
                    $dataContact[$countContacts]["contact_for_support"] =
                        in_array("Support - 1st. Level", $contactFor)
                        || in_array("5 Super Anwender", $contactFor)
                        || in_array("Alle Rollen (1 -7)", $contactFor)
                            ? "Y"
                            : "N";
                    $dataContact[$countContacts]["contact_for_stored_data"] =
                        in_array("Datensicherung Empfänger", $contactFor)
                            ? "Y"
                            : "N";
                }

                if (!isset($this->dictionary[$cells[$cellNumberContactStart][$j]])) {
                    continue;
                }

                $dataContact[$countContacts][$this->dictionary[$cells[$cellNumberContactStart][$j]]] =
                    $this->dictionary[$cells[$cellNumberContactStart][$j]] == "contact_type"
                        ? $this->values[$cells[$i][$j]] ?? null
                        : $cells[$i][$j] ?? '';
            }
            if (
                !$dataContact[$countContacts]["last_name"]
                || !$dataContact[$countContacts]["first_name"]
                || !$dataContact[$countContacts]["contact_type"]
            ) {
                break;
            }

            $countContacts++;
        }
        if ($countContacts != 1) {
            return;
        }

        $dataContact[0]["contact_for_invoice"] = "Y";
        $dataContact[0]["contact_for_contract"] = "Y";
        $dataContact[0]["contact_for_service"] = "Y";
        $dataContact[0]["contact_for_support"] = "Y";
        $dataContact[0]["contact_for_payroll_export"] = "Y";
    }

    private function SetEmployeeCellNumber($cells, &$cellNumberEmployee)
    {
        for ($i = 0; $i < count($cells); $i++) {
            if ($cells[$i][0] != "Lfd. Nr.") {
                continue;
            }

            $cellNumberEmployee = $i;
        }
    }

    public function SetEmployeeData($cells, &$dataEmployee, &$dataCompanyUnit, &$startDate, &$countEmployees)
    {
        $this->SetEmployeeCellNumber($cells, $cellNumberEmployee);

        for ($i = $cellNumberEmployee + 1; $i < count($cells); $i++) {
            $cellNumberProduct = 0;

            for ($j = 0; $j < count($cells[$i]); $j++) {
                if (!$cells[$i][3] && !$cells[$i][2]) {
                    break;
                }

                if (
                    $cells[$cellNumberEmployee][$j] == "Reisekostenbeleg-verwaltung - Komfort\n (Ja=1/nein=leer)"
                    && !in_array(
                        $cells[$cellNumberEmployee][$j + 1],
                        [
                            "Reisekostenbeleg-verwaltung - Light (Ja=1/Nein=leer)",
                            "Reisekostenbeleg-verwaltung - Light\n (Ja=1/nein=leer)",
                        ]
                    )
                ) {
                    continue;
                }

                if (array_key_exists($cells[$cellNumberEmployee][$j], $this->productList)) {
                    $cellNumberProduct = $j;
                }

                if (isset($this->productList[$cells[$cellNumberEmployee][$cellNumberProduct]])) {
                    if (isset($this->optionListSwitch[$cells[$cellNumberEmployee][$j]])) {
                        if (
                            $cells[$cellNumberEmployee][$j] == "Lohnoption \n(leer = Zusätzlich / W = Wandlung)" ||
                            $cells[$cellNumberEmployee][$j] == "Lohnoption \n(leer = Zusätzlich / W = Wandlung / M = W max)"
                        ) {
                            $value = $this->values[$cells[$i][$j]] ?? "Z";
                        } elseif ($cells[$cellNumberEmployee][$j] == "Sachbezug Beleg Option (1=monatlich;2=jählich)") {
                            $value = $this->valueReceiptOption[$cells[$i][$j]] ?? null;
                        } elseif (
                            $cells[$cellNumberEmployee][$j] == "Zuzahlung Sachbezugswert AN (3,30 Euro) verpflichtend (ja=1/nein=leer)" ||
                            $cells[$cellNumberEmployee][$j] == "Zuzahlung Sachbezugswert AN (3,40 Euro) verpflichtend (Ja=1/Nein=2/Leer=Unternehmenswert)" ||
                            $cells[$cellNumberEmployee][$j] == "Automatische jährliche Erhöhung DEM-Wert laut Gesetzgeber? (Ja=1/Nein=2/Leer=Unternehmenswert)" ||
                            $cells[$cellNumberEmployee][$j] == "Automatische jährliche Erhöhung DEM-Wert laut Gesetzgeber? (Ja=1 / nein=leer)" ||
                            $cells[$cellNumberEmployee][$j] == "Automatische jährliche Erhöhung DEM/DEG-Wert laut Gesetzgeber? (Ja=1/Nein=2/Leer=Unternehmenswert)" ||
                            $cells[$cellNumberEmployee][$j] == "Automatische jährliche Erhöhung Sachbezugswert laut Gesetzgeber? (Ja=1 / nein=leer)"
                        ) {
                            $value = $this->values[$cells[$i][$j]] ?? null;
                        } else {
                            $value = $this->values[$cells[$i][$j]] ?? "N";
                        }

                        $optionCode = $this->productList[$cells[$cellNumberEmployee][$cellNumberProduct]] . $this->optionListSwitch[$cells[$cellNumberEmployee][$j]];
                        $optionID = Option::GetOptionIDByCode($optionCode);
                        $dataEmployee[$countEmployees]["OptionList"][] = array(
                            "option_id" => $optionID,
                            "value" => $value
                        );

                        if (($optionCode == OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY || $optionCode == OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY) && $value == "Y") {
                            $dataCompanyUnit["OptionList"][] = array(
                                "option_id" => $optionID,
                                "value" => $value
                            );
                        }
                    } elseif (isset($this->dictionary[trim($cells[$cellNumberEmployee][$j])])) {
                        if ($this->productList[$cells[$cellNumberEmployee][$cellNumberProduct]] . $this->dictionary[trim($cells[$cellNumberEmployee][$j])] == OPTION__AD__MAIN__PAYMENT_MONTH_QTY) {
                            $dataEmployee[$countEmployees]["OptionList"][] = array(
                                "option_id" => Option::GetOptionIDByCode($this->productList[$cells[$cellNumberEmployee][$cellNumberProduct]] . $this->dictionary[trim($cells[$cellNumberEmployee][$j])]),
                                "value" => 1
                            );
                        } else {
                            if($this->dictionary[trim($cells[$cellNumberEmployee][$j])] == "__max_yearly") {
                                $value = trim(str_replace(",", "", $cells[$i][$j]));
                            } elseif ($cells[$cellNumberEmployee][$j] == "Gutschein Kategorie \n(leer = Unternehmens-einstellung) ") {
                                $value = $this->voucherCategoryList[$cells[$i][$j]];
                            } elseif ($cells[$cellNumberEmployee][$j] == "Gutschein Szenario\n(leer = Unternehmens-einstellung) ") {
                                $value = $this->voucherScenario[
                                    $this->voucherScenarioList[$cells[$i][$j]]
                                ];
                            } else {
                                $value = $cells[$i][$j];
                            }

                            $dataEmployee[$countEmployees]["OptionList"][] = array(
                                "option_id" => Option::GetOptionIDByCode($this->productList[$cells[$cellNumberEmployee][$cellNumberProduct]].$this->dictionary[trim($cells[$cellNumberEmployee][$j])]),
                                "value" => $value
                            );
                        }
                    } elseif (isset($this->bonusVoucher[trim($cells[$cellNumberEmployee][$j])])) {
                        $dataEmployee[$countEmployees]["bonusVoucher"][$this->bonusVoucher[trim($cells[$cellNumberEmployee][$j])]] = $cells[$i][$j];
                    } else {
                        if (!isset($cells[$i][$j]) || $cells[$i][$j] == !1 || $cells[$i][$j] == !'1') {
                            continue;
                        }

                        $dataEmployee[$countEmployees]["ContractList"][] = array(
                            "product_id" => Product::GetProductIDByCode($this->productList[$cells[$cellNumberEmployee][$cellNumberProduct]]),
                            "start_date" => $startDate
                        );
                        if ($this->productList[$cells[$cellNumberEmployee][$cellNumberProduct]] == PRODUCT__FOOD__MAIN) {
                            $j++;
                        }

                        $dataCompanyUnit['ContractList'][] = array(
                            "product_id" => Product::GetProductIDByCode($this->productList[$cells[$cellNumberEmployee][$cellNumberProduct]]),
                            "start_date" => $startDate
                        );
                    }
                } elseif (isset($this->dictionary[$cells[$cellNumberEmployee][$j]])) {
                    if ($this->dictionary[$cells[$cellNumberEmployee][$j]] == "material_status") {
                        $dataEmployee[$countEmployees][$this->dictionary[$cells[$cellNumberEmployee][$j]]] = $this->values[$cells[$i][$j]] ?? null;
                    } elseif ($this->dictionary[$cells[$cellNumberEmployee][$j]] == "birthday") {
                        $dataEmployee[$countEmployees][$this->dictionary[$cells[$cellNumberEmployee][$j]]] = isset($cells[$i][$j]) ? date(
                            'Y-m-d',
                            strtotime($cells[$i][$j])
                        ) : null;
                    } else {
                        $dataEmployee[$countEmployees][$this->dictionary[$cells[$cellNumberEmployee][$j]]] = $cells[$i][$j] ?? '';
                    }
                }
            }
            if (!empty($dataEmployee[$countEmployees]) && !empty($dataEmployee[$countEmployees]["ContractList"])) {
                $dataEmployee[$countEmployees]["ContractList"][] = array(
                    "product_id" => Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
                    "start_date" => $startDate
                );
            }
            $countEmployees++;
        }
    }

    private function InitDictionary()
    {
        $this->values = array(
            "Geschäftsführer/Eigentümer" => "management",
            "Personalmanager" => "hr",
            "Finanzmanager" => "finance",
            "IT-Manager" => "it",
            "Sonst." => "other",
            "Ledig" => "single",
            "ledig" => "single",
            "Single" => "single",
            "Verheiratet" => "married",
            "verheiratet" => "married",
            "1" => "Y",
            "2" => "N",
            "Ja" => "Y",
            "ja" => "Y",
            "nein" => "N",
            "W" => "W",
            "w" => "W",
            "Wandlung" => "W",
            "Zusätzlich" => "Z",
            "letzter Monat" => "last_month",
            "aktueller Monat" => "current_month",
            );
        $this->valueReceiptOption = array(
            "1" => "monthly",
            "2" => "yearly",
            "monatlich" => "monthly",
            "jählich" => "yearly",
        );
        $this->dictionary = array(
            "Unternehmensname:" => "title",
            "KD.Nr. trebono Cloud Service" => "customer_guid",
            "Strasse:" => "street",
            "Strasse" => "street",
            "Hausnummer:" => "house",
            "Hausnummer" => "house",
            "Haus-nummer" => "house",
            "PLZ:" => "zip_code",
            "PLZ" => "zip_code",
            "Ort:" => "city",
            "Ort" => "city",
            "Land:" => "country",
            "Land" => "country",
            "Tel. Nummer Zentrale:" => "phone",
            "Ust.ID:" => "vat_payer_id",
            "HRB Nr.:" => "register",
            "HR Nr.:" => "register",
            "Mandanten ID (z.B. Datev):\nin der Lohnabrechnungssoftware" => "client_id",
            "Bank Name:" => "bank_details",
            "IBAN:" => "iban",
            "BIC: (nur wenn IBAN NICHT mit DE beginnt)" => "bic",
            "Stichtag Lohnexport:" => "financial_statement_date",
            "Format Import Lohnabrechnung:" => "datev_format",
            "Lohnabrechner ID (z.B. Datev)\nin der Lohnabrechnungssoftware" => "tax_consultant",
            "LAN Sachbezugswert Hauptmahlzeit mit pauschale Versteuerung (Essensmarke)" => "acc_meal_value_tax_flat",
            "LAN Steuerfreier Essenszuschuss" => "acc_food_subsidy_tax_free",
            "LAN Bruttogehalt" => "acc_gross_salary",
            "LAN Sachkosten-zuschuss (44 Euro)" => "acc_grant_of_materials",
            "LAN Sachkostenzuschuss (44 Euro)" => "acc_grant_of_materials",
            "LAN Internetkostenzuschuss pauschal versteuert" => "acc_internet_subsidy_tax",
            "LAN Internetkosten zuschuss pauschal versteuert" => "acc_internet_subsidy_tax",
            "LAN Internetkosten-zuschuss pauschal versteuert" => "acc_internet_subsidy_tax",
            "LAN Handykosten zuschuss frei" => "acc_mobile_subsidy_tax_free",
            "LAN Handykosten-zuschuss frei" => "acc_mobile_subsidy_tax_free",
            "LAN Erholungskosten-zuschuss pauschal versteuert" => "acc_recreation_subsidy_tax_flat",
            "LAN Erholungskostenzuschuss pauschal versteuert" => "acc_recreation_subsidy_tax_flat",
            "LAN Nettobezug (Werbung)" => "acc_net_income",
            "LAN Prämie 37b" => "acc_bonus_tax_flat",
            "LAN Job-Ticket" => "acc_transport_tax_free",
            "LAN Kita" => "acc_child_care_tax_free",
            "LAN Reisekostenerstattung" => "acc_travel_tax_free",
            "LAN Geschenke" => "acc_gift",
            "LAN BGM" => "acc_corporate_health_management",
            "Basis SEPA Nummer" => "sepa_service",
            "Firmen SEPA Nummer" => "sepa_voucher",
            "Unterschrift Datum" => "_date",
            "Anrede" => "salutation",
            "Nachname" => "last_name",
            "Vorname" => "first_name",
            "Titel" => "position",
            "Rolle" => "contact_type",
            "Abteilung" => "department",
            "E-Mail" => "email",
            "E-Mail (Bitte nicht vergessen!)" => "email",
            "Mobilnummer" => "phone",
            "Festnetz" => "phone_job",
            "Mobil Nummer" => "phone",
            "Geburtstag" => "birthday",
            "Familienstand" => "material_status",
            "Anzahl Kinder" => "child_count",
            "Eintrittsdatum" => "start_date",
            "Personalnummer" => "employee_guid",
            "Kostenstelle" => "cost_center_number",
            "Bank Name" => "bank_name",
            "IBAN" => "iban",
            "BIC" => "bic",
            "Arbeitstage pro Woche" => "working_days_per_week",
            "Max. dig. Essensmarken pro Monat (empfohlen max. 15)" => "__units_per_month",
            "Max. dig. Essensmarken pro Monat (max. 15)" => "__units_per_month",
            "Essenszuschuss AG (max. 3,10 Euro für Essensmarke 6,40 Euro)" => "__employer_meal_grant",
            "Essenszuschuss AG (max. 3,10 Euro für Essensmarke 6,50 Euro)" => "__employer_meal_grant",
            "Essenszuschuss AG (leer = max. Betrag von 3,10 €)" => "__employer_meal_grant",
            "max. monatlicher Betrag" => "__employer_grant",
            "max. monatlicher Betrag\n(max. 21,25 Euro)" => "__employer_grant",
            "max. monatlicher Betrag\n(kein Limit)" => "__employer_grant",
            "max. monatlicher Betrag \n(max. 44 Euro)" => "__employer_grant",
            "max. Betrag pro Monat" => "__max_monthly",
            "Max. jährl. Betrag" => "__max_yearly",
            "Max. jährl. Betrag\n(max. 255 Euro)" => "__max_yearly",
            "max. jährlicher Betrag\n(max. 156 € ledig / +104 € Frau /+ je Kind 52 €)" => "__max_value",
            "Abrechnungsmonat für jährlichen Betrag" => "__payment_month",
            "max. jährlicher Betrag" => "__max_value",
            "% Reduktion Handyfinanzierung" => "__age_deduction",
            "max. Anzahl Geschenke pro Jahr" => "__units_per_year",
            "max. Betrag pro Geschenk" => "__employer_grant",
            "max. Betrag pro Geschenk\n(max. 60 €)" => "__employer_grant",
            "max. Budget pro Monat" => "__max_monthly",
            "max. Budget pro Jahr" => "__max_yearly",
            "max. Betrag" => "__max_yearly",
            "Dauer der Zahlung in Monaten (min. 1 - max. 12)" => "__payment_month_qty",
            "Dauer der Zahlung je Beleg in Monaten (min. 1 - max. 12)" => "__payment_month_qty",
            "Abrechnungsmonat" => "payroll_month",
            "Ind. Text Registrierung E-Mail" => "reg_email_text",
            "Gutschein Kategorie \n(leer = Unternehmens-einstellung)" => "__default_reason",
            "Gutschein Szenario\n(leer = Unternehmens-einstellung)" => "__default_reason_scenario"
        );
        $this->parentDictionary = array(
            "Name \nMutter Organisation:" => "title",
            "KD.Nr. trebono Cloud Service" => "customer_guid",
        );
        $this->productList = array(
            "Digitale Essensmarke Service (Ja=1/nein=leer)" => PRODUCT__FOOD__MAIN,
            "Digitale Essensmarke (DEM) Service (Ja=1/nein=leer)" => PRODUCT__FOOD__MAIN,
            "Plausibilitäts-prüfung Beleg Service  (Ja=1/nein=leer)" => PRODUCT__FOOD__PLAUSIBILITY,
            "Prüfung Pauschal-versteuerungs-freiheit service (Ja=1/nein=leer)" => PRODUCT__FOOD__LUMP_SUM_TAX_EXAMINATION,
            "Wochen-einkauf Service  (Ja=1/nein=leer)" => PRODUCT__FOOD__WEEKLY_SHOPPING,
            "Kantinen-nutzung Service  (Ja=1/nein=leer)" => PRODUCT__FOOD__CANTEEN,
            "Sachbezug Service (max. 44 Euro/Monat)\n (Ja=1/nein=leer)" => PRODUCT__BENEFIT__MAIN,
            "Digitaler Essensgutschein (DEG) Service (Ja=1/nein=leer)" => PRODUCT__FOOD_VOUCHER__MAIN,
            "Essen Gutschein Service (EGS) (Ja=1/nein=leer)" => PRODUCT__FOOD_VOUCHER__MAIN,
            "Digitaler Essen Gutschein Service (DEG) (Ja=1/nein=leer)" => PRODUCT__FOOD_VOUCHER__MAIN,
            "Sachbezug Gutschein Service (max. 44 Euro/Monat)\n (Ja=1/nein=leer)" => PRODUCT__BENEFIT_VOUCHER__MAIN,
            "Internet Service (Ja=1/nein=leer)" => PRODUCT__INTERNET__MAIN,
            "Werbung Service (Ja=1/nein=leer)" => PRODUCT__AD__MAIN,
            "Erholungsbeihilfe Service\n (Ja=1/nein=leer)" => PRODUCT__RECREATION__MAIN,
            "Handy Service\n (Ja=1/nein=leer)" => PRODUCT__MOBILE__MAIN,
            "Geschenk Service\n (Ja=1/nein=leer)" => PRODUCT__GIFT_VOUCHER__MAIN,
            "Geschenk Gutschein\n (Ja=1/nein=leer)" => PRODUCT__GIFT_VOUCHER__MAIN,
            "Prämien Service\n (Ja=1/nein=leer)" => PRODUCT__BONUS_VOUCHER__MAIN,
            "Prämien Gutschein\n (Ja=1/nein=leer)" => PRODUCT__BONUS_VOUCHER__MAIN,
            "Job Ticket\n (Ja=1/nein=leer)" => PRODUCT__TRANSPORT__MAIN,
            "Kita Service\n (Ja=1/nein=leer)" => PRODUCT__CHILD_CARE__MAIN,
            "Reisekostenbeleg-verwaltung\n (Ja=1/nein=leer)" => PRODUCT__TRAVEL__MAIN,
            "Reisekostenbeleg-verwaltung - Komfort\n (Ja=1/nein=leer)" => PRODUCT__TRAVEL__MAIN,
            "Reisekostenbeleg-verwaltung - Light (Ja=1/nein=leer)" => PRODUCT__TRAVEL__MAIN,
            "Reisekostenbeleg-verwaltung - Light\n (Ja=1/nein=leer)" => PRODUCT__TRAVEL__MAIN,
            "Reisekostenbeleg-verwaltung (Ja=1/Nein=leer)" => PRODUCT__TRAVEL__MAIN,
            "Betriebliches Gesundheits Management (BGM) (Ja=1/Nein=leer)" => PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN
        );
        $this->optionListSwitch = array(
            "Lohnoption \n(leer = Zusätzlich / W = Wandlung)" => "__salary_option",
            "Lohnoption \n(leer = Zusätzlich / W = Wandlung / M = W max)" => "__salary_option",
            "Zuzahlung Sachbezugswert AN (3,30 Euro) verpflichtend (ja=1/nein=leer)" => "__employee_meal_grant_mandatory",
            "Zuzahlung Sachbezugswert AN (3,40 Euro) verpflichtend (Ja=1/Nein=2/Leer=Unternehmenswert)" => "__employee_meal_grant_mandatory",
            "Automatische jährliche Erhöhung DEM-Wert laut Gesetzgeber? (Ja=1 / nein=leer)" => "__auto_adoption",
            "Automatische jährliche Erhöhung Sachbezugswert laut Gesetzgeber? (Ja=1 / nein=leer)" => "__auto_adoption",
            "Automatische jährliche Erhöhung DEM-Wert laut Gesetzgeber? (Ja=1/Nein=2/Leer=Unternehmenswert)" => "__auto_adoption",
            "Automatische jährliche Erhöhung DEM/DEG-Wert laut Gesetzgeber? (Ja=1/Nein=2/Leer=Unternehmenswert)" => "__auto_adoption",
            "Sachbezug Beleg Option (1=monatlich;2=jählich)" => "__receipt_option"
        );
        $this->bonusVoucher = array(
            "max. Betrag" => "amount",
            "Erstellungs-datum" => "voucher_date",
            "Verwendungszweck" => "reason",
            "Prämien Grund" => "reason",
            "Intervall (einmalig=leer/monatl.=1/ jährlich=2)" => "recurring_frequency"
        );
        $this->giftVoucher = array(
            "max. Betrag" => "amount",
            "Erstellungs-datum" => "voucher_date",
            "Verwendungszweck" => "reason",
            "Prämien Grund" => "reason",
            "Intervall (einmalig=leer/monatl.=1/ jährlich=2)" => "recurring_frequency"
        );
        $this->voucherScenario = ["exchangeable", "company_flex", "company", "employee_flex", "employee"];
        $this->voucherScenarioList = [
            "I - Eintauschbare Gutschein Kategorie" => 0,
            "II - Unternehmens Gutschein Kategorie flex" => 1,
            "III - Unternehmens Gutschein Kategorie" => 2,
            "IV - Mitarbeiter Gutschein Kategorie flex" => 3,
            "V - Mitarbeiter Gutschein Kategorie" => 4,
        ];
        $this->voucherCategory = [
            "Scenario Sachbezug Gutschein:" => OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO,
            "Scenario Prämien Gutschein:" => OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON_SCENARIO,
            "Category Sachbezug Gutschein:" => OPTION__BENEFIT_VOUCHER__MAIN__DEFAULT_REASON,
            "Category Prämien Gutschein:" => OPTION__BONUS_VOUCHER__MAIN__DEFAULT_REASON,
        ];
        $this->voucherCategoryList = [
            "Alles für meine gesunde Ernährung" => 0,
            "Alles für deine Ernährung" => 1,
            "Alles was mein Auto bewegt" => 2,
            "Alles für deine Fitness" => 3,
            "Alles für dein Haus" => 4,
            "Alles für deine digitale Welt" => 5,
            "Alles für deinen Haushalt" => 6,
            "Alles für deine Mode, Kosmetik und Schönheit" => 7,
        ];
    }
}
