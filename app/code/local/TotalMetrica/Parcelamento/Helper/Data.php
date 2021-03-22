<?php

/**
 * Parcelamento Helper
 * 
 * @category    Parcelamento
 * @package     TotalMetrica_Parcelamento
 * @author      César Martins
 */
class TotalMetrica_Parcelamento_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getInstallmentQuantity($productId = null) {
        $_product = Mage::getModel('catalog/product')->load($productId);

        $_store = $_product->getStore();
        $_convertedFinalPrice = $_store->roundPrice($_store->convertPrice($_product->getFinalPrice()));
        $_finalPrice = Mage::helper('tax')->getPrice($_product, $_convertedFinalPrice);

        //$installmentQuantity = $_product->getData('installment_quantity');
        $installmentQuantity = Mage::getStoreConfig('parcelamento/general/installment_quantity');
        
        $html = '';
        if ($installmentQuantity){
            //$price = $_finalPrice/$installmentQuantity;
            $price = $this->valorDaParcelaDivisao($_finalPrice);
            $showPrice = Mage::helper('core')->currency($price["valor_parcelas"], true, false);

            $text = '<span class="installment1">Ou até </span>'.'<span class="installment2">'.$price["qtd_parcelas"].'x</span>'.'<span class="installment3"> de </span>'.'<span class="installment5">'.$showPrice.'</span>'.'<span class="installment4"> sem juros.</span>';
            $html .= "<br/><span style='color: #05c944' class='installment_quantity'>$text</span>";

        }
        return $html;
    }

    public function getValorMinimoParcela(){

        $valorMinParcela = Mage::getStoreConfig('parcelamento/general/parcelamento_valor_minimo_parcela');
        return $valorMinParcela;

    }
    public function getQuantidadeMaxParcelamento(){

        $qtdMaxParcelas = Mage::getStoreConfig('parcelamento/general/parcelamento_quantity');
        return $qtdMaxParcelas;

    }

    //HOME
    public function getInstallmentPayment($productId = null) {

        $_product = Mage::getModel('catalog/product')->load($productId);
        //$installmentPayment = $_product->getData('attr_payment');

        $maxsemjuros = Mage::getStoreConfig('parcelamento/general/parcelamento_quantity_sem_juros');
        $valor_parcela = $_product->getFinalPrice() / $maxsemjuros;
        $html = '';
        $corHexadecimal = Mage::getStoreConfig('parcelamento/general/parcelamento_cor_hexadecimal');
        $qtdMaxParcelas = Mage::getStoreConfig('parcelamento/general/parcelamento_quantity');

        $price = $this->valorDaParcelaDivisao($_product->getFinalPrice(), $qtdMaxParcelas);

        //$text = '<span class="installment1">Ou até </span>' . '<span class="installment2">' . $price["qtd_parcelas"] . 'x</span>' . '<span class="installment3"> de </span>' . '<span class="installment5">' . number_format($price["valor_parcelas"], 2, ",", ".") . '</span>' . '<span class="installment4"> sem juros.</span>';
        //$html .= "<p style='color: " . $corHexadecimal . "' class='installment_quantity'>$text ";


        $html .= "<p style='color:". $corHexadecimal . ";font-size:15px;' class='installment_quantity'>Ou em até " . $price["qtd_parcelas"] . "x de " . number_format($price["valor_parcelas"], 2, ",", ".") . " sem juros.</p>";

        return $html;
    }
    /*
     public function getInstallmentPayment($productId = null) {

        $_product = Mage::getModel('catalog/product')->load($productId);
        //$installmentPayment = $_product->getData('attr_payment');

        $maxsemjuros = Mage::getStoreConfig('parcelamento/general/parcelamento_quantity_sem_juros');
        $valor_parcela = $_product->getFinalPrice() / $maxsemjuros;
        $html = '';
        $installmentPaymentText = Mage::getStoreConfig('parcelamento/general/parcelamento_payment_text');

        $price = $this->valorDaParcelaDivisao($_product->getFinalPrice());

        //$text = '<span class="installment1">Ou até </span>' . '<span class="installment2">' . $price["qtd_parcelas"] . 'x</span>' . '<span class="installment3"> de </span>' . '<span class="installment5">' . number_format($price["valor_parcelas"], 2, ",", ".") . '</span>' . '<span class="installment4"> sem juros.</span>';
        //$html .= "<br/><span style='color: #05c944' class='installment_quantity'>$text ";

        //if ($installmentPaymentText) {
        //    $text = $this->__($installmentPaymentText);
        //    $html .= "$text";
        //}
        

        //$url1 = parse_url("https://www.camellia.com.br/");
        //$url2 = parse_url(Mage::getBaseUrl());

        $br='';      
        $url2 = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url_home="www.camellia.com.br/productfilterpro/ajax/loadproducts/";
        
        if( $url_home == $url2 ) 
        {
            $br = "<br>";
        }
       
        
        $html ="<p style='color:#ef836a;font-size:15px;' class='installment_quantity'>Ou em até " . $price["qtd_parcelas"] . "x de " . number_format($price["valor_parcelas"], 2, ",", ".") . " sem juros.</p>";   
       

        return $html;
    }*/

    public function getEnableModule(){
        $enableModule = Mage::getStoreConfig('parcelamento/general/enable');
        return $enableModule;
    }

    public function getListInstallmentQuantity($productId = null) {

        $html = '';
        $_product = Mage::getModel('catalog/product')->load($productId);

        $_store = $_product->getStore();
        $_convertedFinalPrice = $_store->roundPrice($_store->convertPrice($_product->getFinalPrice()));
        $_finalPrice = Mage::helper('tax')->getPrice($_product, $_convertedFinalPrice);

        $installmentQuantity = Mage::getStoreConfig('parcelamento/general/parcelamento_quantity');
        $maxsemjuros = Mage::getStoreConfig('parcelamento/general/parcelamento_quantity_sem_juros');
        //$maxparcelas = Mage::getStoreConfig('parcelapagproduto/configuracoes/parcelamento_quantity');

        if($this->validadeApiMercadoLivre()){

            $api = Mage::getStoreConfig('parcelamento/general/parcelamento_api_mercado_pago');
            $retornoApi = $this->getJsonAPiMercadoLivre($api, $_finalPrice);

            $contadorParceas = 1;
            foreach ($retornoApi as $key => $values){

                if($installmentQuantity >= $contadorParceas){
                    $html .= $values->recommended_message . "<br>";
                }
                $contadorParceas++;
            }

        }else{

            $div = "<div class='installment-titulo'>
                        <div class='parcelamento-titulo' style='padding: 10px;border: 1px solid #ccc;'>
                            <img src='" . Mage::getBaseUrl() . "media/parcelamento/pptransparente-cards.png'>
                        </div>
                        <div class='parcelamento-parcelas' style='padding: 10px;border: 1px solid #ccc;'>";

            for ($i = 1; $i <= $installmentQuantity; $i++){

                //sem juros
                if($maxsemjuros >= $i){
                    $valor_parcela = $_product->getFinalPrice() / $i;

                    //$text = '<span class="installment2">'.$i.'x de number_format($valor_parcela, 2, ",", ".")</span>';
                    $html .= "<p class='installment_quantity-lista box'>" . $i . "x de R$ " . number_format($valor_parcela, 2, ",", ".") . "</p>";

                }else{

                    $juros = Mage::getStoreConfig('parcelamento/general/parcelamento_taxa_juros');
                    $percetual = $juros / 100;
                    $valor_parcela = $_product->getFinalPrice()*$percetual*pow((1+$percetual),$i)/(pow((1+$percetual),$i)-1);

                    $text = '<span class="installment2">'.$i.'x</span>'.'<span class="installment3"> de </span>'.'<span class="installment5">'.number_format($valor_parcela, 2, ",", ".").'</span>'.'<span class="installment4"> com juros.</span>';
                    $html .= "<span class='installment_quantity-lista-juros'>$text</span><br>";

                }

            }

//
//            if ($installmentQuantity){
//                $price = $_finalPrice/$installmentQuantity;
//                $showPrice = Mage::helper('core')->currency($price, true, false);
//                $text = '<span class="installment1">Ou até </span>'.'<span class="installment2">'.$installmentQuantity.'x</span>'.'<span class="installment3"> de </span>'.'<span class="installment5">'.$showPrice.'</span>'.'<span class="installment4"> sem juros.</span>';
//                $html .= "<br/><span style='color: #05c944' class='installment_quantity'>$text</span>";
//            }

        }

        return $div . $html . "</div>
                        <div class='parcelamento-titulo-boleto' style='padding: 10px;border: 1px solid #ccc;'>
                            <div class='div-imagem-boleto'>
                                <img src='" . Mage::getBaseUrl() . "media/parcelamento/boleto-logo.png'>
                            </div>
                            <div class='valor-boleto'>
                                <b>R$" . number_format($_product->getFinalPrice(), 2, ",", ".") . "</b>
                            </div>
                        </div>";
    }

    public function getEnableShowOther(){
        $enableShowOther = Mage::getStoreConfig('parcelamento/general/parcelamento_payment_show_other');
        return $enableShowOther;
    }

    public function getJsonAPiMercadoLivre($api, $valor){

        $url = "https://api.mercadopago.com/v1/payment_methods/parcelamentos?public_key=" . $api . "&payment_type_id=credit_card&payment_method_id=master&amount=" . $valor . "#json";
        $ApiML = Mage::getSingleton('core/session')->getApiML();

        if(empty($ApiML)){
            $json = file_get_contents($url);
            $obj = json_decode($json);
            Mage::getSingleton('core/session')->setApiML($obj[0]->payer_costs);
            $obj = $obj[0]->payer_costs;
        }else{
            //refazer isso quando limpar o cache!!!
            $obj = $ApiML[0]->payer_costs;
        }
        return $obj;

    }

    public function validadeApiMercadoLivre(){

        return Mage::getStoreConfig('parcelamento/general/parcelamento_usar_mercado_pago');

    }

    public function valorDaParcelaDivisao($valor, $qtdMaxParcelas, $parcelas = ''){

        $valorMinParcela = Mage::getStoreConfig('parcelamento/general/parcelamento_valor_minimo_parcela');

        if($valor > $valorMinParcela){

            if(empty($parcelas)) {
                $parcelas = intval($valor / $valorMinParcela);
            }

            $valorParcelas =  $valor / $parcelas;

            if($valorParcelas >= $valorMinParcela){
                if($qtdMaxParcelas >= $parcelas){
                    return array('qtd_parcelas' => $parcelas, 'valor_parcelas' => $valorParcelas);
                }else{
                    $parcelas = $parcelas - 1;
                    return $this->valorDaParcelaDivisao($valor, $qtdMaxParcelas,$parcelas);
                }
            }else{
                $parcelas = $parcelas - 1;
                return $this->valorDaParcelaDivisao($valor, $qtdMaxParcelas,$parcelas);
            }

        }else{
            return array('qtd_parcelas' => 1, 'valor_parcelas' => $valor);
        }

    }

}

