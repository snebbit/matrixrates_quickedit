<?php
/*
Basic UI for editing WebShopApps Premium MatrixRates shipping charges
TC 09-15
*/
require_once('app/Mage.php');
Mage::app();
Mage::getSingleton('core/session', array('name'=>'adminhtml'));

if(Mage::getSingleton('admin/session')->isLoggedIn()){

    $readConnection=Mage::getSingleton('core/resource')->getConnection('core_read');
    $writeConnection=Mage::getSingleton('core/resource')->getConnection('core_write');

    if(isset($_POST['edit-go'])){
        $pk=filter_input(INPUT_POST,'edit-id',FILTER_VALIDATE_INT);
        $newPrice=filter_input(INPUT_POST,'new-price',FILTER_VALIDATE_FLOAT);
        if($pk AND $newPrice){
            $writeConnection->query("update `shipping_premiumrate` set `price`='$newPrice' where `pk`='$pk' limit 1");
            setcookie('success-id',$pk);
            header("Location:delivery.php");
            die;
        }
    }

    $successId=filter_input(INPUT_COOKIE,'success-id',FILTER_VALIDATE_INT);
    if($successId) setcookie('success-id','x',time()-1);

    $shippingRates=$readConnection->fetchAll("select * from `shipping_premiumrate` order by `dest_zip` asc");
    ?>
<!DOCTYPE html>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/floatthead/1.2.13/jquery.floatThead.min.js"></script>
        <style>
            *{ font-family: arial,tahoma,sans-serif; color: #222; }
            #delivery-table{ min-width: 700px; }
            #delivery-table form{ display:none; }
            #delivery-table input{ font-size: 11px; }
            #delivery-table thead tr th,.floatThead-table thead tr th{ background: #ddd; }
            #delivery-table tbody tr:hover td{ background: #f3f3f3; }
            #delivery-table tr td:last-child{ width: 33%; text-align: center; font-size: 20px; }
            td,th{ padding: 8px 12px; }
            #delivery-table td:first-child{ font-size: 28px; }
            #delivery-table span.price-editable:hover{ cursor: pointer; }
            #delivery-table span.price-editable icon{ visibility: hidden; display: inline-table; width: 16px; height: 16px; background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA90lEQVQ4T5XTPytHURzH8dfvGVhQymAw4REopZ/FogzKQHkMymI2eSCegFIGrCb8kj8Dk4WBxSilb51Tp8O97r11p3Pf78/nfM+5A/2eeexjF6+BDnrwc7jAOB6wjLeugllM4hhjKfQ+JF0ECzjDFQ4qydF/gtjzeaodwaeF5BvDNkEN53FlySdGTYImOCQfWMRj0ymU064PKeAVXOeFukEvuG4QcAxs4o+7EclD3NRrucEMLhvg91T7F1w2WMMO1quEVjgLpvGMLWwWkoCj9qjtuscWVnGCr0Ky1AXODfZwmFJCsoE7PHX50aLBNqZwm96XLmD+5gfAsjP7wQAa3gAAAABJRU5ErkJggg==);}
            #delivery-table span.price-editable:hover icon{ visibility: visible;}
        </style>
    </head>
    <body>
        <table id="delivery-table">
            <thead>
                <tr>
                    <th>Postcode</th>
                    <th>Cart Total</th>
                    <th>Cart Weight</th>
                    <th>Shipping Price</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($shippingRates as $rate){?>
                <tr id="row-<?php echo $rate['pk'];?>">
                    <td><?php echo $rate['dest_zip'].(strlen($rate['dest_zip'])<1 ? '<i>['.$rate['dest_country_id'].'] *</i>' : '');?></td>
                    <td>&pound;<?php echo number_format($rate['price_from_value']).' - &pound;'.number_format($rate['price_to_value']);?></td>
                    <td><?php echo number_format($rate['weight_from_value']).' - '.number_format($rate['weight_to_value']);?>kg</td>
                    <td>
                        <span class="price-editable">&pound;<?php echo number_format($rate['price']);?> <icon></icon></span>
                        <form action="" method="post" class="edit-form">
                            <input type="text" size="4" name="new-price" value="<?php echo (float)$rate['price'];?>" />
                            <input type="hidden" name="edit-id" value="<?php echo $rate['pk'];?>" />
                            <input type="submit" name="edit-go" value="Save" />
                            <input type="button" class="edit-cancel" value="Cancel" />
                        </form>
                    </td>
                </tr>
            <?php }?>
            </tbody>
        </table>
        <script>
            $(document).ready(function(){
                var $table = $('#delivery-table');
                $table.floatThead();
                $('span.price-editable').each(function(){
                    $(this).click(function(){
                        $(this).hide();
                        $(this).parent().find('form').show()
                                                     .find('input:text').select();
                    });
                });
                $('input.edit-cancel').each(function(){
                    $(this).click(function(){
                        $(this).parent().hide();
                        $(this).parents('td').find('span').show();
                    });
                });
                <?php if($successId){?>
                    location.href='#row-<?php echo $successId;?>';
                    $('#row-<?php echo $successId;?> td,#row-<?php echo $successId;?> span').css({backgroundColor:'#ccffcc'}).animate({backgroundColor: '#ffffff'},7000);
                <?php }?>
            });
        </script>
    </body>
</html>
<?php }
else echo 'Please log in to the Magento admin panel first.';
?>