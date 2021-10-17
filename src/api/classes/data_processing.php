<?php
require_once "processors/classes/uFunc.php";
require_once "api/classes/api_functions.php";
require_once "uCat/classes/common.php";

class dataProc
{
    public $apiEvotor,
        $source,
        $site_id,
        $cat_id,
        $cat_uuid,
        $cat_title,
        $unit_id,
        $item_id,
        $arr_key,
        $sect_id,
        $child_id,
        $parent_id;

    private $uCore,
        $uFunc,
        $uCat,
        $attach_uuid = [],
        $flag_search_cat,
        $flag_search_sect;

    public function check_data()
    {
        //        if (isset($_POST['upload_method'])) {
        //            $this->source = trim($_POST['upload_method']); // evotor или mad
        //            $this->site_id = site_id;
        //        }
        //        else {
        //            $this->uCore->error(1);
        //        }
        //        $this->source = "mad"; // evotor или mad
        $this->site_id = site_id;
    }

    public function convertsql2text($str)
    {
        $str_conv = uString::sql2text($str, 1);
        $str_conv = str_replace(["\r", "\n"], " ", $str_conv);
        $str_conv = strip_tags($str_conv);
        $str_conv = html_entity_decode($str_conv);
        $str_conv = preg_replace('/ {2,}/', ' ', $str_conv);
        $str_conv = trim($str_conv);
        return $str_conv;
    }

    public function generateEAN()
    {
        $number = rand(100000000, 999999999);
        $code = rand(460, 469) . str_pad($number, 9, '0');
        $weightflag = true;
        $sum = 0;
        for ($i = strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int) $code[$i] * ($weightflag ? 3 : 1);
            $weightflag = !$weightflag;
        }
        $code .= (10 - ($sum % 10)) % 10;
        return $code;
    }

    public function get_items_of_catalog($sect_obj = false)
    {
        $finish_data = [];

        try {
            $get_item_variants = $this->uFunc->pdo("uCat")->prepare("SELECT
            uuid_variant,
            item_title,
            primary_cat_id,
            u235_cats.cat_uuid,
            u235_cats.cat_title,
            u235_sects.sect_uuid as sect_uuid,
            has_variants,
            item_type,
            quantity,
            unit_name,
            item_nds,
            item_price,
            allow_to_sell,
            item_cost_price,
            item_descr,
            item_article_number,
            item_bar_codes,
            var_type_title,
            price,
            var_quantity
            FROM
            items_variants
            LEFT JOIN
            u235_items
              ON
              items_variants.item_id=u235_items.item_id AND
              items_variants.site_id=u235_items.site_id
            LEFT JOIN
            u235_cats
              ON
              u235_items.primary_cat_id=u235_cats.cat_id AND
              u235_items.site_id=u235_cats.site_id
            LEFT JOIN
            u235_sects_cats
              ON
              u235_cats.cat_id=u235_sects_cats.cat_id AND
              u235_cats.site_id=u235_sects_cats.site_id
            LEFT JOIN
            u235_sects
              ON
              u235_sects_cats.sect_id=u235_sects.sect_id AND
              u235_sects_cats.site_id=u235_sects.site_id
            LEFT JOIN
            units
              ON
              u235_items.unit_id=units.unit_id AND
              u235_items.site_id=units.site_id
            LEFT JOIN
            items_variants_types
              ON
              items_variants.var_type_id=items_variants_types.var_type_id AND
              items_variants.site_id=items_variants_types.site_id
            WHERE
            items_variants.site_id=:site_id
            ");

            $get_item_variants->bindParam(
                ':site_id',
                $this->site_id,
                PDO::PARAM_INT
            );
            $get_item_variants->execute();

            for (
                $i = 0;
                ($finish_data[$i] = $get_item_variants->fetch(
                    PDO::FETCH_ASSOC
                ));
                $i++
            ) {
                $finish_data[$i]["evotor_uuid"] =
                    $finish_data[$i]["uuid_variant"];
                unset($finish_data[$i]["uuid_variant"]);
                $finish_data[$i]["item_title"] =
                    $this->convertsql2text($finish_data[$i]["item_title"]) .
                    " (" .
                    $this->convertsql2text($finish_data[$i]["var_type_title"]) .
                    ")";
                unset($finish_data[$i]["var_type_title"]);
                $finish_data[$i]["item_descr"] = $this->convertsql2text(
                    $finish_data[$i]["item_descr"]
                );
                $finish_data[$i]["item_price"] = $finish_data[$i]["price"];
                unset($finish_data[$i]["price"]);
                $finish_data[$i]["quantity"] = $finish_data[$i]["var_quantity"];
                unset($finish_data[$i]["var_quantity"]);
            }
            $this->arr_key = $i;
            unset($finish_data[$this->arr_key]);
        } catch (PDOException $e) {
            $this->uFunc->error('10' . $e->getMessage());
        }

        $has_variants = 0;

        try {
            $get_items = $this->uFunc->pdo("uCat")->prepare("SELECT
            evotor_uuid,
            item_title,
            primary_cat_id,
            u235_cats.cat_uuid,
            u235_cats.cat_title,
            u235_sects.sect_uuid as sect_uuid,
            has_variants,
            item_type,
            quantity,
            unit_name,
            item_nds,
            item_price,
            allow_to_sell,
            item_cost_price,
            item_descr,
            item_article_number,
            item_bar_codes
            FROM
            u235_items
            LEFT JOIN
            u235_cats
              ON
              u235_items.primary_cat_id=u235_cats.cat_id AND
              u235_items.site_id=u235_cats.site_id
            LEFT JOIN
            u235_sects_cats
              ON
              u235_cats.cat_id=u235_sects_cats.cat_id AND
              u235_cats.primary_sect_id=u235_sects_cats.sect_id AND 
              u235_cats.site_id=u235_sects_cats.site_id
            LEFT JOIN
            u235_sects
              ON
              u235_sects_cats.sect_id=u235_sects.sect_id AND
              u235_sects_cats.site_id=u235_sects.site_id
            LEFT JOIN
            units
              ON
              u235_items.unit_id=units.unit_id AND
              u235_items.site_id=units.site_id
            WHERE
            u235_items.has_variants=:has_variants AND 
            u235_items.site_id=:site_id
            ");

            $get_items->bindParam(
                ':has_variants',
                $has_variants,
                PDO::PARAM_INT
            );
            $get_items->bindParam(':site_id', $this->site_id, PDO::PARAM_INT);
            $get_items->execute();

            for (
                $i = $this->arr_key;
                ($finish_data[$i] = $get_items->fetch(PDO::FETCH_ASSOC));
                $i++
            ) {
                $finish_data[$i]["item_title"] = $this->convertsql2text(
                    $finish_data[$i]["item_title"]
                );
                $finish_data[$i]["item_descr"] = $this->convertsql2text(
                    $finish_data[$i]["item_descr"]
                );
            }
            unset($finish_data[$i]);
            $finish_data['sect_obj'] = $sect_obj;

            $this->create_array_and_load_in_market($finish_data);
        } catch (PDOException $e) {
            $this->uFunc->error('15' /*.$e->getMessage()*/);
        }
    }

    public function create_array_and_load_in_market($arraydata)
    {
        $tovardatasects = [];
        $tovardatagroups = [];
        $tovardata = [];
        $groupuuidcode = [];
        $sectuuidcode = [];
        $sect_obj = $arraydata['sect_obj'];
        unset($arraydata['sect_obj']);

        foreach ($arraydata as $key => $value) {
            // $value["has_variants"] пока не включен в обработку
            if (!in_array($value["cat_uuid"], $groupuuidcode)) {
                if ($value["cat_uuid"] !== $value["sect_uuid"]) {
                    if ($value["cat_title"] == "Без категории") {
                        $groupuuidcode[] = $value["cat_uuid"];
                        $cat_bez_cat = $value["cat_uuid"];
                    } else {
                        $groupdataitem = [
                            'uuid' => $value["cat_uuid"],
                            'name' => mb_strimwidth(
                                $value["cat_title"],
                                0,
                                100,
                                '...'
                            ),
                            'group' => true,
                            'parentUuid' => $value["sect_uuid"],
                            'hasVariants' => false,
                            'code' => 'cat-' . $value["primary_cat_id"],
                        ];
                        $groupuuidcode[] = $value["cat_uuid"];
                        $sectuuidcode[] = $value["sect_uuid"];
                        $tovardatagroups[$key] = $groupdataitem;
                    }
                }
            }

            if ($value["item_nds"] == 0) {
                $taxnds = "NO_VAT";
            } elseif ($value["item_nds"] == 1) {
                $taxnds = "VAT_0";
            } elseif ($value["item_nds"] == 2) {
                $taxnds = "VAT_10";
            } elseif ($value["item_nds"] == 3) {
                $taxnds = "VAT_18";
            } elseif ($value["item_nds"] == 4) {
                $taxnds = "VAT_10_110";
            } elseif ($value["item_nds"] == 5) {
                $taxnds = "VAT_18_118";
            } else {
                $taxnds = "NO_VAT";
            }

            $shtrihkod = $value["item_bar_codes"];
            if ($shtrihkod == null) {
                $shtrihkoditem = [$this->generateEAN()];
            } else {
                $shtrihkoditem = [$shtrihkod];
            }

            if ($value["unit_name"] == null) {
                $unit_name = "";
            } else {
                $unit_name = $value["unit_name"];
            }

            $item_price = (float) number_format(
                $value["item_price"],
                3,
                '.',
                ''
            );
            $item_cost_price = (float) number_format(
                $value["item_cost_price"],
                3,
                '.',
                ''
            );

            if (isset($cat_bez_cat)) {
                if ($value["cat_uuid"] == $cat_bez_cat) {
                    $value["cat_uuid"] = null;
                }
            }

            if ($value["item_type"] == 2) {
                $typetovar = "SERVICE";
                $tovardataitem = [
                    'uuid' => $value["evotor_uuid"],
                    'name' => mb_strimwidth(
                        $value["item_title"],
                        0,
                        100,
                        '...'
                    ),
                    'group' => false,
                    'parentUuid' => $value["cat_uuid"],
                    'hasVariants' => false,
                    'type' => $typetovar,
                    'measureName' => $unit_name,
                    'tax' => $taxnds,
                    'price' => $item_price > 9999999.99 ? 0.0 : $item_price,
                    'allowToSell' => filter_var(
                        $value["allow_to_sell"],
                        FILTER_VALIDATE_BOOLEAN
                    ),
                    'description' => $value["item_descr"],
                    'articleNumber' => mb_strimwidth(
                        $value["item_article_number"],
                        0,
                        20
                    ),
                    'code' => $value["primary_cat_id"],
                    'barCodes' => $shtrihkoditem,
                ];
            } else {
                $typetovar = "NORMAL";
                $tovardataitem = [
                    'uuid' => $value["evotor_uuid"],
                    'name' => mb_strimwidth(
                        $value["item_title"],
                        0,
                        100,
                        '...'
                    ),
                    'group' => false,
                    'parentUuid' => $value["cat_uuid"],
                    'hasVariants' => false,
                    'type' => $typetovar,
                    'quantity' => (float) $value["quantity"],
                    'measureName' => $unit_name,
                    'tax' => $taxnds,
                    'price' => $item_price > 9999999.99 ? 0.0 : $item_price,
                    'allowToSell' => filter_var(
                        $value["allow_to_sell"],
                        FILTER_VALIDATE_BOOLEAN
                    ),
                    'costPrice' =>
                        $item_cost_price > 9999999.99 ? 0.0 : $item_cost_price,
                    'description' => $value["item_descr"],
                    'articleNumber' => mb_strimwidth(
                        $value["item_article_number"],
                        0,
                        20
                    ),
                    'code' => '',
                    'barCodes' => $shtrihkoditem,
                ];
            }

            $tovardata[$key] = $tovardataitem;
        }

        if ($sect_obj !== false) {
            foreach ($sect_obj as $key => $value) {
                if ($value->parent_sect_id !== null) {
                    $sect_uuid_obj = $this->get_parent_sect_uuid(
                        $value->parent_sect_id
                    );
                    if ($sect_uuid_obj) {
                        $value->parent_sect_uuid = $sect_uuid_obj[0]->sect_uuid;
                    }

                    unset($value->parent_sect_id);
                } else {
                    if ($value->sect_id == "0" || $value->sect_id == 0) {
                        continue;
                    } else {
                        unset($value->parent_sect_id);
                        $value->parent_sect_uuid = null;
                    }
                }

                $groupdatasect = [
                    'uuid' => $value->sect_uuid,
                    'name' => mb_strimwidth($value->sect_title, 0, 100, '...'),
                    'group' => true,
                    'parentUuid' => $value->parent_sect_uuid,
                    'hasVariants' => false,
                    'code' => 'sect-' . $value->primary_sect_id,
                ];

                if (in_array($value->sect_uuid, $sectuuidcode)) {
                    $tovardatasects[$key] = $groupdatasect;
                }
            }
        }

        $result_group_array = array_merge($tovardatasects, $tovardatagroups);
        $result_array = array_merge($result_group_array, $tovardata);
        $tovardatajson = json_encode($result_array, JSON_UNESCAPED_UNICODE);

        $this->apiEvotor->add_items($tovardatajson);
    }

    public function upload_items_in_internet_market(
        $itemlist,
        $site_id = site_id,
        $item_status = 1
    ) {
        $i_count = count($itemlist) - 1;
        for ($i = $i_count; $i >= 0; $i--) {
            $value = $itemlist[$i];

            $uuid = $value["uuid"]; // string
            $group = $value["group"]; // boolean
            $parentUuid = $value["parentUuid"]; // string or null
            //$hasVariants = $value["hasVariants"]; // boolean
            $hasVariants = 0;
            $name = $value["name"]; // string
            $code = $value["code"]; // string
            $quantity = $value["quantity"]; // int
            //            $attributesChoices = $value["attributesChoices"]; // null
            //            $images = $value["images"]; // null
            $type = $value["type"]; // string
            $barCodes = $value["barCodes"]; // array
            $price = $value["price"]; // int or float
            $costPrice = $value["costPrice"]; // int or float
            $measureName = $value["measureName"]; // string
            $tax = $value["tax"]; // string
            $allowToSell = $value["allowToSell"]; // boolean
            $description = $value["description"]; // string
            $articleNumber = $value["articleNumber"]; // string
            //            // Параметры только для алкогольной продукции ****************
            //            $alcoCodes = $value["alcoCodes"]; // array or null
            //            $alcoholByVolume = $value["alcoholByVolume"]; // float or null
            //            $alcoholProductKindCode = $value["alcoholProductKindCode"]; // int or null
            //            $tareVolume = $value["tareVolume"]; // float or null
            //            // End ****************

            if ($group === false) {
                if ($parentUuid === null) {
                    // группа или товар находятся в корне
                    $primary_cat_id = 0;
                } else {
                    $groupdataitem = [
                        'cat_uuid' => $parentUuid,
                        'cat_title' => rand(100, 500),
                        'parentUuid' => null,
                        'code' => null,
                        'site_id' => $site_id,
                    ];
                    $primary_cat_id = $this->create_new_cat($groupdataitem);
                }

                if ($type == "SERVICE") {
                    $typeint = 2;
                } else {
                    $typeint = 0;
                }

                if ($tax == "NO_VAT") {
                    $taxint = 0;
                } elseif ($tax == "VAT_0") {
                    $taxint = 1;
                } elseif ($tax == "VAT_10") {
                    $taxint = 2;
                } elseif ($tax == "VAT_18") {
                    $taxint = 3;
                } elseif ($tax == "VAT_10_110") {
                    $taxint = 4;
                } elseif ($tax == "VAT_18_118") {
                    $taxint = 5;
                } else {
                    $taxint = 0;
                }

                $dataitem = [
                    'evotor_uuid' => $uuid,
                    'item_title' => $name,
                    'parentUuid' => $parentUuid,
                    'allow_to_sell' => $allowToSell,
                    'quantity' => $quantity,
                    'has_variants' => $hasVariants,
                    'item_type' => $typeint,
                    'item_bar_codes' => $barCodes,
                    'item_article_number' => $articleNumber,
                    'item_price' => $price,
                    'item_cost_price' => $costPrice,
                    'item_descr' => $description,
                    'item_nds' => $taxint,
                    'measureName' => $measureName,
                    'primary_cat_id' => $primary_cat_id,
                    'code' => $code,
                    'item_status' => $item_status,
                    'site_id' => $site_id,
                ];

                if (
                    $last_item_id = $this->search_uuid_in_items_and_variants(
                        $uuid
                    )
                ) {
                    // Обновляем информацию о товаре
                    $item_info = [
                        'uuid' => $uuid,
                        'quantity' => $quantity,
                        'item_nds' => $taxint,
                        'allow_to_sell' => $allowToSell,
                        'item_title' => $name,
                        'item_descr' => $description,
                        'item_price' => $price,
                        'item_cost_price' => $costPrice,
                        'item_article_number' => $articleNumber,
                        'bar_code' => $barCodes,
                        'item_type' => $typeint,
                        'primary_cat_id' => $primary_cat_id,
                    ];

                    $this->update_item_info($item_info);
                } else {
                    $last_item_id = $this->create_new_item($dataitem);
                }

                // Если еще не прикреплены, то прикрепляем
                if (
                    !$this->search_attach_item2cat(
                        $last_item_id,
                        $primary_cat_id
                    )
                ) {
                    $this->uCat->attach_item2cat(
                        $primary_cat_id,
                        $last_item_id,
                        false
                    );
                }
            } else {
                $this->flag_search_cat = false;
                $this->flag_search_sect = false;

                if ($this->search_uuid_in_cats($uuid)) {
                    $cat_info = [
                        'uuid' => $uuid,
                        'cat_title' => $name,
                        'cat_descr' => $description,
                    ];
                    $this->update_cat_info($cat_info);
                    $this->attach_uuid[$uuid] = "cat";
                    $this->flag_search_cat = true;
                }

                if ($this->search_uuid_in_sects($uuid)) {
                    $sect_info = [
                        'uuid' => $uuid,
                        'sect_title' => $name,
                        'sect_descr' => $description,
                    ];
                    $this->update_sect_info($sect_info);
                    $this->attach_uuid[$uuid] = "sect";
                    $this->flag_search_sect = true;
                }

                if ($this->flag_search_cat && $this->flag_search_sect) {
                    if (!$this->search_attach_cat2sect($uuid, $uuid)) {
                        $this->uCat->attach_cat2sect(
                            $this->sect_id,
                            $this->cat_id,
                            false
                        );
                    }
                    $this->attach_uuid[$uuid] = "sect";
                }

                if (!$this->flag_search_cat && !$this->flag_search_sect) {
                    if (!$this->search_uuid_in_sects($uuid)) {
                        $sect_info = [
                            'uuid' => $uuid,
                            'sect_title' => $name,
                            'sect_descr' => $description,
                            'primary_sect_id' => 0,
                        ];
                        $this->create_new_sect($sect_info);
                        $this->attach_uuid[$uuid] = "sect";
                    }
                }

                if ($parentUuid !== null) {
                    if ($this->search_uuid_in_sects($parentUuid)) {
                        $sect_info = [
                            'uuid' => $parentUuid,
                            'sect_title' => $name,
                            'sect_descr' => $description,
                        ];
                        $this->update_sect_info($sect_info);
                        $this->attach_uuid[$parentUuid] = "sect";
                    } else {
                        $sect_info = [
                            'uuid' => $parentUuid,
                            'sect_title' => $name,
                            'sect_descr' => $description,
                            'primary_sect_id' => 0,
                        ];
                        $this->create_new_sect($sect_info);
                        $this->attach_uuid[$parentUuid] = "sect";
                    }
                }

                if (isset($this->attach_uuid[$uuid])) {
                    if ($this->attach_uuid[$uuid] == "cat") {
                        if (
                            !$this->search_attach_cat2sect($uuid, $parentUuid)
                        ) {
                            $this->uCat->attach_cat2sect(
                                $this->sect_id,
                                $this->cat_id,
                                false
                            );
                        }
                    } else {
                        if ($parentUuid !== null) {
                            if (
                                !$this->search_attach_sect2sect(
                                    $uuid,
                                    $parentUuid
                                )
                            ) {
                                $this->uCat->attach_sect2sect(
                                    $this->parent_id,
                                    $this->child_id,
                                    false
                                );
                            }
                        }
                    }
                }
            }
        }

        if ($cat_obj = $this->get_cats()) {
            foreach ($cat_obj as $key => $value) {
                $this->uCat->calculate_cat_item_count($value);
                $this->uCat->calculate_cat_sect_count($value);
            }
        }

        return $last_item_id;
    }

    private function search_uuid_in_cats($uuid, $site_id = site_id)
    {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            primary_sect_id
            FROM
            u235_cats
            WHERE
            cat_uuid=:cat_uuid AND 
            site_id=:site_id
            ");

            $stm->bindParam(':cat_uuid', $uuid, PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $res = $stm->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                return true;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('170' /*.$e->getMessage()*/);
        }

        return false;
    }

    private function update_cat_info($data, $site_id = site_id)
    {
        $uuid = $data["uuid"];
        $cat_title = $data["cat_title"];
        $cat_descr = $data["cat_descr"];

        try {
            $upd_cat = $this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_cats
            SET 
            cat_title=:cat_title,
            cat_descr=:cat_descr
            WHERE
            cat_uuid=:cat_uuid AND 
            site_id=:site_id
            ");

            $upd_cat->bindParam(':cat_uuid', $uuid, PDO::PARAM_STR);
            $upd_cat->bindParam(':cat_title', $cat_title, PDO::PARAM_STR);
            $upd_cat->bindParam(':cat_descr', $cat_descr, PDO::PARAM_STR);
            $upd_cat->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $upd_cat->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('180' /*.$e->getMessage()*/);
        }
    }

    private function search_uuid_in_items_and_variants(
        $uuid,
        $site_id = site_id
    ) {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            item_id
            FROM
            u235_items
            WHERE
            evotor_uuid=:item_uuid AND 
            site_id=:site_id
            ");

            $stm->bindParam(':item_uuid', $uuid, PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $res = $stm->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                return $res["item_id"];
            }
        } catch (PDOException $e) {
            $this->uFunc->error('185' /*.$e->getMessage()*/);
        }

        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            item_id
            FROM
            items_variants
            WHERE
            uuid_variant=:var_uuid AND 
            site_id=:site_id
            ");

            $stm->bindParam(':var_uuid', $uuid, PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $res = $stm->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                return $res["item_id"];
            }
        } catch (PDOException $e) {
            $this->uFunc->error('190' /*.$e->getMessage()*/);
        }

        return false;
    }

    private function update_item_info($data, $site_id = site_id)
    {
        $uuid = $data["uuid"];
        $quantity = $data["quantity"];
        $item_nds = $data["item_nds"];
        $allow_to_sell = $data["allow_to_sell"];
        $item_title = $data["item_title"];
        $item_descr = $data["item_descr"];
        $item_price = $data["item_price"];
        $item_cost_price = $data["item_cost_price"];
        $item_article_number = $data["item_article_number"];
        if (!empty($data["bar_code"])) {
            $bar_code = $data["bar_code"][0];
        } else {
            $bar_code = "";
        }
        $item_type = $data["item_type"];
        $primary_cat_id = $data["primary_cat_id"];

        try {
            $upd_items = $this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_items
            SET 
            quantity=:quantity,
            item_nds=:item_nds,
            allow_to_sell=:allow_to_sell,
            item_title=:item_title,
            item_descr=:item_descr,
            item_price=:item_price,
            item_cost_price=:item_cost_price,
            item_article_number=:item_article_number,
            item_bar_codes=:item_bar_codes,
            item_type=:item_type,
            primary_cat_id=:primary_cat_id
            WHERE  
            evotor_uuid=:evotor_uuid AND
            site_id=:site_id
            ");

            $upd_items->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $upd_items->bindParam(':evotor_uuid', $uuid, PDO::PARAM_STR);
            $upd_items->bindParam(':quantity', $quantity, PDO::PARAM_STR);
            $upd_items->bindParam(':item_nds', $item_nds, PDO::PARAM_INT);
            $upd_items->bindParam(
                ':allow_to_sell',
                $allow_to_sell,
                PDO::PARAM_INT
            );
            $upd_items->bindParam(':item_title', $item_title, PDO::PARAM_STR);
            $upd_items->bindParam(':item_descr', $item_descr, PDO::PARAM_STR);
            $upd_items->bindParam(':item_price', $item_price, PDO::PARAM_STR);
            $upd_items->bindParam(
                ':item_cost_price',
                $item_cost_price,
                PDO::PARAM_STR
            );
            $upd_items->bindParam(
                ':item_article_number',
                $item_article_number,
                PDO::PARAM_STR
            );
            $upd_items->bindParam(':item_bar_codes', $bar_code, PDO::PARAM_STR);
            $upd_items->bindParam(':item_type', $item_type, PDO::PARAM_INT);
            $upd_items->bindParam(
                ':primary_cat_id',
                $primary_cat_id,
                PDO::PARAM_INT
            );
            $upd_items->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('210' /*.$e->getMessage()*/);
        }
    }

    private function search_attach_item2cat(
        $item_id,
        $cat_id,
        $site_id = site_id
    ) {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            cat_id
            FROM
            u235_cats_items
            WHERE
            item_id=:item_id AND
            cat_id=:cat_id AND 
            site_id=:site_id
            ");

            $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $res = $stm->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                return true;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('220' /*.$e->getMessage()*/);
        }

        return false;
    }

    private function search_uuid_in_sects($uuid, $site_id = site_id)
    {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            primary_sect_id
            FROM
            u235_sects
            WHERE
            sect_uuid=:sect_uuid AND 
            site_id=:site_id
            ");

            $stm->bindParam(':sect_uuid', $uuid, PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $res = $stm->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                return true;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('230' /*.$e->getMessage()*/);
        }

        return false;
    }

    private function update_sect_info($data, $site_id = site_id)
    {
        $uuid = $data["uuid"];
        $sect_title = $data["sect_title"];
        $sect_descr = $data["sect_descr"];

        try {
            $upd_cat = $this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_sects
            SET 
            sect_title=:sect_title,
            sect_descr=:sect_descr
            WHERE
            sect_uuid=:sect_uuid AND 
            site_id=:site_id
            ");

            $upd_cat->bindParam(':sect_uuid', $uuid, PDO::PARAM_STR);
            $upd_cat->bindParam(':sect_title', $sect_title, PDO::PARAM_STR);
            $upd_cat->bindParam(':sect_descr', $sect_descr, PDO::PARAM_STR);
            $upd_cat->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $upd_cat->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('240' /*.$e->getMessage()*/);
        }
    }

    private function create_new_sect($data, $site_id = site_id)
    {
        $uuid = $data["uuid"];
        $sect_title = $data["sect_title"];
        $sect_descr = $data["sect_descr"];
        if ($sect_descr == null) {
            $sect_descr = "";
        }
        $primary_sect_id = $data["primary_sect_id"];

        try {
            $search_sect_id = $this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id
            FROM
            u235_sects
            WHERE
            site_id=:site_id
            ORDER BY
            sect_id DESC
            LIMIT 1
            ");

            $search_sect_id->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $search_sect_id->execute();

            $row = $search_sect_id->fetch(PDO::FETCH_OBJ);

            if (!$row) {
                $sect_id = 1;
            } else {
                $sect_id = $row->sect_id + 1;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('250' /*.$e->getMessage()*/);
        }

        try {
            $insert_sect = $this->uFunc->pdo("uCat")->prepare("INSERT INTO
            u235_sects
            (sect_id, sect_uuid, sect_title, seo_title, sect_descr, seo_descr, show_cats_descr, primary_sect_id, site_id) 
            VALUES (
            :sect_id,
            :sect_uuid,
            :sect_title,
            :seo_title,
            :sect_descr,
            :seo_descr,
            :show_cats_descr,
            :primary_sect_id,
            :site_id
            )
            ");

            $empty_str = "";
            $insert_sect->bindParam(':sect_id', $sect_id, PDO::PARAM_INT);
            $insert_sect->bindParam(':sect_uuid', $uuid, PDO::PARAM_STR);
            $insert_sect->bindParam(':sect_title', $sect_title, PDO::PARAM_STR);
            $insert_sect->bindParam(':seo_title', $empty_str, PDO::PARAM_STR);
            $insert_sect->bindParam(':sect_descr', $sect_descr, PDO::PARAM_STR);
            $insert_sect->bindParam(':seo_descr', $empty_str, PDO::PARAM_STR);
            $insert_sect->bindParam(
                ':show_cats_descr',
                $empty_str,
                PDO::PARAM_STR
            );
            $insert_sect->bindParam(
                ':primary_sect_id',
                $primary_sect_id,
                PDO::PARAM_INT
            );
            $insert_sect->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $insert_sect->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('260' /*.$e->getMessage()*/);
        }
    }

    private function get_sect_id_to_uuid($uuid, $site_id = site_id)
    {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id
            FROM
            u235_sects
            WHERE
            sect_uuid=:sect_uuid AND
            site_id=:site_id
            ");

            $stm->bindParam(':sect_uuid', $uuid, PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $res = $stm->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                return (int) $res["sect_id"];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('270' /*.$e->getMessage()*/);
        }

        return false;
    }

    private function get_cat_id_to_uuid($uuid, $site_id = site_id)
    {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            cat_id
            FROM
            u235_cats
            WHERE
            cat_uuid=:cat_uuid AND
            site_id=:site_id
            ");

            $stm->bindParam(':cat_uuid', $uuid, PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $res = $stm->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                return (int) $res["cat_id"];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('280' /*.$e->getMessage()*/);
        }

        return false;
    }

    private function search_attach_cat2sect(
        $cat_uuid,
        $sect_uuid,
        $site_id = site_id
    ) {
        if ($sect_uuid == null) {
            $this->sect_id = 0;
        } else {
            $this->sect_id = $this->get_sect_id_to_uuid($sect_uuid);
        }
        $this->cat_id = $this->get_cat_id_to_uuid($cat_uuid);

        if ($this->sect_id !== false && $this->cat_id !== false) {
            try {
                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
                sect_id
                FROM
                u235_sects_cats
                WHERE
                sect_id=:sect_id AND
                cat_id=:cat_id AND 
                site_id=:site_id
                ");

                $stm->bindParam(':sect_id', $this->sect_id, PDO::PARAM_INT);
                $stm->bindParam(':cat_id', $this->cat_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $res = $stm->fetch(PDO::FETCH_ASSOC);

                if ($res) {
                    return true;
                }
            } catch (PDOException $e) {
                $this->uFunc->error('290' /*.$e->getMessage()*/);
            }
        }

        return false;
    }

    private function search_attach_sect2sect(
        $uuid,
        $parentUuid,
        $site_id = site_id
    ) {
        $this->child_id = $this->get_sect_id_to_uuid($uuid);
        $this->parent_id = $this->get_sect_id_to_uuid($parentUuid);

        if ($this->child_id !== false && $this->parent_id !== false) {
            try {
                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
                parent_sect_id
                FROM
                sects_sects
                WHERE
                parent_sect_id=:parent_sect_id AND
                child_sect_id=:child_sect_id AND 
                site_id=:site_id
                ");

                $stm->bindParam(
                    ':parent_sect_id',
                    $this->parent_id,
                    PDO::PARAM_INT
                );
                $stm->bindParam(
                    ':child_sect_id',
                    $this->child_id,
                    PDO::PARAM_INT
                );
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $res = $stm->fetch(PDO::FETCH_ASSOC);

                if ($res) {
                    return true;
                }
            } catch (PDOException $e) {
                $this->uFunc->error('320' /*.$e->getMessage()*/);
            }
        }

        return false;
    }

    public function create_new_cat($data)
    {
        // parentUuid индификатор группы или null
        // code может содержать primary_cat_id (т.е. u235_cats.cat_id), если изначально товары и группы загружались в кассу с нашего интернет-магазина
        $this->site_id = $data["site_id"];
        $this->cat_uuid = $data["cat_uuid"];
        $this->cat_title = $data["cat_title"];

        $goto_market = 0;
        check_cat:
        try {
            $search_cat = $this->uFunc->pdo("uCat")->prepare("SELECT
            cat_uuid
            FROM
            u235_cats
            WHERE
            site_id=:site_id AND 
            cat_uuid=:cat_uuid
            ");

            $search_cat->bindParam(':site_id', $this->site_id, PDO::PARAM_INT);
            $search_cat->bindParam(
                ':cat_uuid',
                $this->cat_uuid,
                PDO::PARAM_STR
            );
            $search_cat->execute();

            $row = $search_cat->fetch(PDO::FETCH_ASSOC);

            if (!$row["cat_uuid"]) {
                if ($goto_market == 1) {
                    $this->cat_title = "Новая категория" . rand(1, 100);
                }
                try {
                    $select_cat_id = $this->uFunc->pdo("uCat")->prepare("SELECT
                    cat_id
                    FROM
                    u235_cats
                    WHERE
                    site_id=:site_id
                    ORDER BY
                    cat_id DESC
                    LIMIT 1
                    ");

                    $select_cat_id->bindParam(
                        ':site_id',
                        $this->site_id,
                        PDO::PARAM_INT
                    );
                    $select_cat_id->execute();

                    $res = $select_cat_id->fetch(PDO::FETCH_OBJ);

                    if (!$res) {
                        $this->cat_id = 1;
                    } else {
                        $this->cat_id = $res->cat_id + 1;
                    }
                } catch (PDOException $e) {
                    $this->uFunc->error('330' /*.$e->getMessage()*/);
                }

                $empty_string = "";

                try {
                    $insert_cat = $this->uFunc->pdo("uCat")
                        ->prepare("INSERT INTO
                    u235_cats
                    (cat_id, cat_uuid, cat_title, cat_url, cat_descr, site_id) 
                    VALUES (
                    :cat_id,
                    :cat_uuid,
                    :cat_title,
                    :cat_url,
                    :cat_descr,
                    :site_id
                    )
                    ");

                    $insert_cat->bindParam(
                        ':site_id',
                        $this->site_id,
                        PDO::PARAM_INT
                    );
                    $insert_cat->bindParam(
                        ':cat_id',
                        $this->cat_id,
                        PDO::PARAM_INT
                    );
                    $insert_cat->bindParam(
                        ':cat_uuid',
                        $this->cat_uuid,
                        PDO::PARAM_STR
                    );
                    $insert_cat->bindParam(
                        ':cat_title',
                        $this->cat_title,
                        PDO::PARAM_STR
                    );
                    $insert_cat->bindParam(
                        ':cat_url',
                        $empty_string,
                        PDO::PARAM_STR
                    );
                    $insert_cat->bindParam(
                        ':cat_descr',
                        $empty_string,
                        PDO::PARAM_STR
                    );
                    $insert_cat->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('340' /*.$e->getMessage()*/);
                }
            }

            if (isset($data["parentUuid"])) {
                if ($data["parentUuid"] !== null && $goto_market == 0) {
                    $this->cat_uuid = $data["parentUuid"];
                    $goto_market = 1;
                    goto check_cat;
                }
            }

            try {
                $sel_cat_id = $this->uFunc->pdo("uCat")->prepare("SELECT
                cat_id
                FROM
                u235_cats
                WHERE
                site_id=:site_id AND 
                cat_uuid=:cat_uuid
                ");

                $sel_cat_id->bindParam(
                    ':site_id',
                    $this->site_id,
                    PDO::PARAM_INT
                );
                $sel_cat_id->bindParam(
                    ':cat_uuid',
                    $data["cat_uuid"],
                    PDO::PARAM_STR
                );
                $sel_cat_id->execute();

                $row = $sel_cat_id->fetch(PDO::FETCH_OBJ);

                if (!$row) {
                    return null;
                } else {
                    return $row->cat_id;
                }
            } catch (PDOException $e) {
                $this->uFunc->error('350' /*.$e->getMessage()*/);
            }
        } catch (PDOException $e) {
            $this->uFunc->error('360' /*.$e->getMessage()*/);
        }
    }

    public function create_new_item($data)
    {
        // code может содержать primary_cat_id, если изначально товары в кассу загружались с нашего интернет-магазина
        $this->site_id = $data["site_id"];

        $evotor_uuid = $data["evotor_uuid"];
        $item_title = uString::text2sql($data["item_title"], 1);
        $parentUuid = $data["parentUuid"];
        $allow_to_sell = (int) $data["allow_to_sell"];
        $quantity = (float) $data["quantity"];
        $has_variants = (int) $data["has_variants"];
        $item_type = $data["item_type"];
        $item_bar_codes = $data["item_bar_codes"];
        $item_article_number = $data["item_article_number"];
        $item_price = (float) $data["item_price"];
        $item_cost_price = (float) $data["item_cost_price"];
        $item_descr = uString::text2sql($data["item_descr"], 1);
        $item_nds = $data["item_nds"];
        $measureName = trim($data["measureName"]);
        $primary_cat_id = (int) $data["primary_cat_id"];
        $code = $data["code"];
        $item_status = $data["item_status"];

        if ($parentUuid !== null) {
            $parentitem = [
                'cat_uuid' => $parentUuid,
                'cat_title' => 'Новая категория' . rand(1, 100),
                'site_id' => $this->site_id,
            ];

            if (!$this->search_uuid_in_cats($parentUuid)) {
                $this->cat_id = $this->create_new_cat($parentitem);
            }
        }

        if (isset($item_bar_codes[0])) {
            if ($item_bar_codes[0] === null) {
                $bar_code = (string) $this->generateEAN();
            } else {
                $bar_code = (string) $item_bar_codes[0];
            }
        }

        try {
            $search_unit = $this->uFunc->pdo("uCat")->prepare("SELECT
            unit_id
            FROM
            units
            WHERE
            site_id=:site_id AND 
            unit_name=:unit_name
            ");

            $search_unit->bindParam(':site_id', $this->site_id, PDO::PARAM_INT);
            $search_unit->bindParam(':unit_name', $measureName, PDO::PARAM_STR);
            $search_unit->execute();

            $row = $search_unit->fetch(PDO::FETCH_OBJ);

            if (!$row) {
                if (!empty($measureName)) {
                    try {
                        $insert_unit = $this->uFunc->pdo("uCat")
                            ->prepare("INSERT INTO
                        units
                        (unit_name,site_id)
                        VALUES (
                        :unit_name,
                        :site_id)
                        ");

                        $insert_unit->bindParam(
                            ':site_id',
                            $this->site_id,
                            PDO::PARAM_INT
                        );
                        $insert_unit->bindParam(
                            ':unit_name',
                            $measureName,
                            PDO::PARAM_STR
                        );
                        $insert_unit->execute();

                        $this->unit_id = $this->uFunc
                            ->pdo("uCat")
                            ->lastInsertId();
                    } catch (PDOException $e) {
                        $this->uFunc->error('370' /*.$e->getMessage()*/);
                    }
                }
            } else {
                $this->unit_id = $row->unit_id;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('380' /*.$e->getMessage()*/);
        }

        try {
            $search_items = $this->uFunc->pdo("uCat")->prepare("SELECT
            item_id
            FROM
            u235_items
            WHERE
            site_id=:site_id AND 
            evotor_uuid=:evotor_uuid
            ");

            $search_items->bindParam(
                ':site_id',
                $this->site_id,
                PDO::PARAM_INT
            );
            $search_items->bindParam(
                ':evotor_uuid',
                $evotor_uuid,
                PDO::PARAM_STR
            );
            $search_items->execute();

            $row = $search_items->fetch(PDO::FETCH_OBJ);

            if (!$row) {
                try {
                    $search_id_items = $this->uFunc->pdo("uCat")
                        ->prepare("SELECT
                    item_id
                    FROM
                    u235_items
                    WHERE
                    site_id=:site_id
                    ORDER BY
                    item_id DESC
                    LIMIT 1
                    ");

                    $search_id_items->bindParam(
                        ':site_id',
                        $this->site_id,
                        PDO::PARAM_INT
                    );
                    $search_id_items->execute();

                    $row = $search_id_items->fetch(PDO::FETCH_OBJ);

                    if (!$row) {
                        $this->item_id = 1;
                    } else {
                        $this->item_id = $row->item_id + 1;
                    }
                } catch (PDOException $e) {
                    $this->uFunc->error('390' /*.$e->getMessage()*/);
                }

                try {
                    $nullvalue = 0;
                    $cat_count = 1;
                    $insert_item = $this->uFunc->pdo("uCat")
                        ->prepare("INSERT INTO
                    u235_items
                    (item_id,
                    item_avail,
                    evotor_uuid,
                    quantity,
                    unit_id,
                    item_nds,
                    allow_to_sell,
                    item_title,
                    item_descr,
                    item_price,
                    item_cost_price,
                    item_article_number,
                    item_bar_codes,
                    item_type,
                    primary_cat_id,
                    cat_count,
                    item_status,
                    site_id)
                    VALUES (
                    :item_id,
                    :item_avail,
                    :evotor_uuid,
                    :quantity,
                    :unit_id,
                    :item_nds,
                    :allow_to_sell,
                    :item_title,
                    :item_descr,
                    :item_price,
                    :item_cost_price,
                    :item_article_number,
                    :item_bar_codes,
                    :item_type,
                    :primary_cat_id,
                    :cat_count,
                    :item_status,
                    :site_id)
                    ");

                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':site_id',
                        $this->site_id,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_status',
                        $item_status,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_avail',
                        $nullvalue,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_id',
                        $this->item_id,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':quantity',
                        $quantity,
                        PDO::PARAM_STR
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':unit_id',
                        $this->unit_id,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_nds',
                        $item_nds,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':allow_to_sell',
                        $allow_to_sell,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_title',
                        $item_title,
                        PDO::PARAM_STR
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_descr',
                        $item_descr,
                        PDO::PARAM_STR
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_price',
                        $item_price,
                        PDO::PARAM_STR
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_cost_price',
                        $item_cost_price,
                        PDO::PARAM_STR
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_article_number',
                        $item_article_number,
                        PDO::PARAM_STR
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_bar_codes',
                        $bar_code,
                        PDO::PARAM_STR
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':item_type',
                        $item_type,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':primary_cat_id',
                        $primary_cat_id,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':cat_count',
                        $cat_count,
                        PDO::PARAM_INT
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->bindParam(
                        ':evotor_uuid',
                        $evotor_uuid,
                        PDO::PARAM_STR
                    );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $insert_item->execute();

                    return $this->item_id;
                } catch (PDOException $e) {
                    $this->uFunc->error('400' /*.$e->getMessage()*/);
                }
            } else {
                $last_item_id = $row->item_id;

                try {
                    $upd_items = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                    u235_items
                    SET 
                    quantity=:quantity,
                    item_nds=:item_nds,
                    
                    allow_to_sell=:allow_to_sell,
                    item_title=:item_title,
                    item_descr=:item_descr,
                    item_price=:item_price,
                    item_cost_price=:item_cost_price,
                    item_article_number=:item_article_number,
                    item_bar_codes=:item_bar_codes,
                    item_type=:item_type,
                    primary_cat_id=:primary_cat_id
                    WHERE
                    site_id=:site_id AND
                    item_id=:item_id AND 
                    evotor_uuid=:evotor_uuid
                    ");

                    $upd_items->bindParam(
                        ':site_id',
                        $this->site_id,
                        PDO::PARAM_INT
                    );
                    $upd_items->bindParam(
                        ':item_id',
                        $last_item_id,
                        PDO::PARAM_INT
                    );
                    $upd_items->bindParam(
                        ':evotor_uuid',
                        $evotor_uuid,
                        PDO::PARAM_STR
                    );
                    $upd_items->bindParam(
                        ':quantity',
                        $quantity,
                        PDO::PARAM_INT
                    );
                    $upd_items->bindParam(
                        ':item_nds',
                        $item_nds,
                        PDO::PARAM_INT
                    );
                    $upd_items->bindParam(
                        ':allow_to_sell',
                        $allow_to_sell,
                        PDO::PARAM_INT
                    );
                    $upd_items->bindParam(
                        ':item_title',
                        $item_title,
                        PDO::PARAM_STR
                    );
                    $upd_items->bindParam(
                        ':item_descr',
                        $item_descr,
                        PDO::PARAM_STR
                    );
                    $upd_items->bindParam(
                        ':item_price',
                        $item_price,
                        PDO::PARAM_INT
                    );
                    $upd_items->bindParam(
                        ':item_cost_price',
                        $item_cost_price,
                        PDO::PARAM_INT
                    );
                    $upd_items->bindParam(
                        ':item_article_number',
                        $item_article_number,
                        PDO::PARAM_STR
                    );
                    $upd_items->bindParam(
                        ':item_bar_codes',
                        $bar_code,
                        PDO::PARAM_STR
                    );
                    $upd_items->bindParam(
                        ':item_type',
                        $item_type,
                        PDO::PARAM_INT
                    );
                    $upd_items->bindParam(
                        ':primary_cat_id',
                        $primary_cat_id,
                        PDO::PARAM_INT
                    );
                    $upd_items->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('410' /*.$e->getMessage()*/);
                }
                return $last_item_id;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('420' /*.$e->getMessage()*/);
        }
    }

    public function cart_proc($reserv, $action)
    {
        //        $reserv = array(
        //            "21026673-0c22-49b0-9adc-bd4f16d11836" => 1,
        //            "68f67f14-c60a-4ca7-9618-3925739312bf" => 1
        //        );
        //        $action = create or die;
        $data = $this->apiEvotor->create_or_die_reserv($reserv, $action);

        foreach ($data as $key => $value) {
            try {
                $upd_items = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                u235_items
                SET 
                quantity=:quantity
                WHERE
                evotor_uuid=:evotor_uuid
                ");

                $upd_items->bindParam(':evotor_uuid', $key, PDO::PARAM_STR);
                $upd_items->bindParam(':quantity', $value, PDO::PARAM_STR);
                $upd_items->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('430' /*.$e->getMessage()*/);
            }
        }

        $this->get_items_of_catalog();
    }

    public function update_catalog($quantity, $unit_id, $cost_price)
    {
        // ВРЕМЕННАЯ ФУНКЦИЯ
        try {
            $upd_items = $this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_items
            SET 
            quantity=:quantity,
            unit_id=:unit_id,
            item_cost_price=:cost_price
            WHERE
            site_id=:site_id
            ");

            $upd_items->bindParam(':site_id', $this->site_id, PDO::PARAM_INT);
            $upd_items->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $upd_items->bindParam(':unit_id', $unit_id, PDO::PARAM_INT);
            $upd_items->bindParam(':cost_price', $cost_price, PDO::PARAM_INT);
            $upd_items->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('440' /*.$e->getMessage()*/);
        }
    }

    public function create_full_object_and_load_in_terminal()
    {
        $sect_obj = $this->get_sects();
        $this->get_items_of_catalog($sect_obj);
    }

    private function get_parent_sect_uuid($sect_id, $site_id = site_id)
    {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            sect_uuid
            FROM
            u235_sects
            WHERE
            sect_id=:sect_id AND 
            site_id=:site_id
            LIMIT 1
            ");

            $stm->bindParam(':sect_id', $sect_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $sect_uuid_obj = $stm->fetchAll(PDO::FETCH_OBJ);

            return $sect_uuid_obj;
        } catch (PDOException $e) {
            $this->uFunc->error('450' /*.$e->getMessage()*/);
        }
    }

    private function get_parent_cat_uuid($cat_id, $site_id = site_id)
    {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            cat_uuid
            FROM
            u235_cats
            WHERE
            cat_id=:cat_id AND 
            site_id=:site_id
            LIMIT 1
            ");

            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $cat_uuid_obj = $stm->fetchAll(PDO::FETCH_OBJ);

            return $cat_uuid_obj;
        } catch (PDOException $e) {
            $this->uFunc->error('500' /*.$e->getMessage()*/);
        }
    }

    private function get_sects($site_id = site_id)
    {
        $cat_count = 0;
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            u235_sects.sect_id,
            u235_sects.sect_uuid,
            u235_sects.sect_title,
            u235_sects.primary_sect_id,
            sects_sects.parent_sect_id
            FROM
            u235_sects
            LEFT JOIN
            sects_sects
              ON
              u235_sects.sect_id=sects_sects.child_sect_id AND
              u235_sects.site_id=sects_sects.site_id
            WHERE
            u235_sects.site_id=:site_id AND 
            u235_sects.cat_count>:cat_count
            ");

            $stm->bindParam(':cat_count', $cat_count, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);

            return $sect_obj;
        } catch (PDOException $e) {
            $this->uFunc->error('560' /*.$e->getMessage()*/);
        }

        return false;
    }

    private function get_cats($site_id = site_id)
    {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            cat_id
            FROM
            u235_cats
            WHERE
            site_id=:site_id
            ");

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            $cat_obj = $stm->fetchAll(PDO::FETCH_COLUMN);

            return $cat_obj;
        } catch (PDOException $e) {
            $this->uFunc->error('630' /*.$e->getMessage()*/);
        }

        return false;
    }

    function __construct(&$uCore)
    {
        $this->uCore = &$uCore;
        $this->uFunc = new \processors\uFunc($this->uCore);
        $this->apiEvotor = new apiEvotor($this->uCore);
        $this->uCat = new \uCat\common($this->uCore);
        $this->check_data();
        //
        //        if ($this->source == "mad") {
        //            $this->get_items_of_catalog();
        //            //$this->update_catalog(19,1,10);
        //        }
        //        else if ($this->source == "evotor") {
        //            $itemlist = $this->apiEvotor->list_items();
        //            $this->upload_items_in_internet_market($itemlist);
        //        }
    }
}
