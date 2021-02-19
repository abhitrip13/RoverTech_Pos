<?php
include_once 'connectdb.php';

session_start();
if ($_SESSION['useremail'] == "" or $_SESSION['role'] == '') {
    header('location:index.php');
}

function fill_product($pdo,$pid){
$output="";

$select=$pdo->prepare('select * from tbl_product order by pname asc');

$select->execute();

$result=$select->fetchAll();

foreach($result as $row){
    $output.='<option value="'.$row['pid'].'"';
    if($pid==$row['pid']){
        $output.='selected';
    }
    $output.='>'.$row['pname'].'</option>';
}

return $output;

}
//for text fields to show info from tbl_invoice
$id=$_GET['id'];
$select=$pdo->prepare("select * from tbl_invoice where invoice_id=".$id);
$select->execute();

$row=$select->fetch(PDO::FETCH_ASSOC);

$customer_name=$row['customer_name'];
$order_date=date('Y-m-d',strtotime($row['order_date']));
$subtotal=$row["subtotal"];
$tax=$row['tax'];
$discount=$row['discount'];
$total = $row['total']; 
$paid = $row['paid']; 
$due = $row['due']; 
$payment_type = $row['payment_type']; 

//for table to show the invoice_details 

$select=$pdo->prepare("select * from tbl_invoice_details where invoice_id=".$id);
$select->execute();

$row_invoice_details=$select->fetchAll(PDO::FETCH_ASSOC);



if(isset($_POST['btnupdateorder'])){

    //1.get values from the text fields and from the array in variables

    //Variables for the tbl_invoice table
    $txt_customer_name=$_POST['txtcustomer'];
    $txt_order_date=date('Y-m-d',strtotime($_POST['orderdate']));
    $txt_subtotal=$_POST["txtsubtotal"];
    $txt_tax=$_POST['txttax'];
    $txt_discount=$_POST['txtdiscount'];
    $txt_total = $_POST['txttotal']; 
    $txt_paid = $_POST['txtpaid']; 
    $txt_due = $_POST['txtdue']; 
    $txt_payment_type = $_POST['rb']; 

    // variables for the tbl_invoice_details table
    $arr_productid=$_POST['productid']; 
    $arr_productname=$_POST['productname']; 
    $arr_stock=$_POST['stock']; 
    $arr_qty=$_POST['qty']; 
    $arr_price=$_POST['price']; 
    $arr_total=$_POST['total']; 

    //basically first we are reversing everything back to how it was before the order was created , reversing stock and deleting current order and then we will be creating a new order
    // 2.Write update query for tbl_product stock

    foreach($row_invoice_details as $item_invoice_details){
        $updateproduct = $pdo->prepare("update tbl_product set pstock=pstock+".$item_invoice_details['qty']." where pid=".$item_invoice_details['product_id']);

        $updateproduct->execute();
    }

    // 3.Write delelte query for tbl_invoice_details table data where invoice_id=pid
    $delete_invoice_details = $pdo->prepare("delete from tbl_invoice_details where invoice_id=".$id);
    $delete_invoice_details->execute();

    //after reversing everything back we try to create a new order from scratch
    // 4.Write update query for tbl_invoice table data
    $update_invoice = $pdo->prepare("update tbl_invoice set customer_name=:cust, order_date=:orderdate, subtotal=:stotal, tax=:tax, discount=:disc, total=:total, paid=:paid, due=:due, payment_type=:ptype where invoice_id = ".$id); 
    
    $update_invoice->bindParam(':cust', $txt_customer_name);
    $update_invoice->bindParam(':orderdate', $txt_order_date);
    $update_invoice->bindParam(':stotal', $txt_subtotal);
    $update_invoice->bindParam(':tax', $txt_tax);
    $update_invoice->bindParam(':disc', $txt_discount);
    $update_invoice->bindParam(':total', $txt_total);
    $update_invoice->bindParam(':paid', $txt_paid);
    $update_invoice->bindParam(':due', $txt_due);
    $update_invoice->bindParam(':ptype', $txt_payment_type);
    $update_invoice->execute();



    //Query for the invoice_details table(this table will basically contain all the items differently in the invoice_details table for one customer all different items he has chosen)
    $invoice_id=$pdo->lastInsertId();//to get the last inserted id from the previous above query and keeping track if the id of that row
    if($invoice_id!=null){
        for($i=0;$i<count($arr_productid);$i++){

            // 5.Write select query for tbl_product table to get out stock value

            $selectpdt=$pdo->prepare("select * from tbl_product where pid=".$arr_productid[$i]);
            $selectpdt->execute();
            while($rowpdt=$selectpdt->fetch(PDO::FETCH_OBJ)){
                $db_stock[$i]=$rowpdt->pstock;


                $rem_qty=$db_stock[$i]-$arr_qty[$i];

            if($rem_qty<0){
                return "order not complete";
            }else{
            // 6. Write update query for tbl_product table to update stock values
            
                $update=$pdo->prepare("update tbl_product set pstock ='$rem_qty' where pid=".$arr_productid[$i]);
                $update->execute();
            }
            }

            // 7.Write insert query for tbl_invoice_details for insert new records
            $insert = $pdo->prepare("insert into tbl_invoice_details(invoice_id, product_id, product_name, qty, price, order_date) values(:invid, :pid, :pname, :qty, :price, :orderdate)"); 
            $insert->bindParam(':invid', $id);
            $insert->bindParam(':pid', $arr_productid[$i]);
            $insert->bindParam(':pname', $arr_productname[$i]);
            $insert->bindParam(':qty', $arr_qty[$i]);
            $insert->bindParam(':price', $arr_price[$i]);
            $insert->bindParam(':orderdate', $txt_order_date);
            $insert->execute(); 
        }
        header("location:orderlist.php");
    }
    }


    if ($_SESSION['role'] == 'Admin') {
        include_once 'header.php';
    }else{
        include_once 'headeruser.php';
    } ?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 style="font-size:30px;" class="m-0">Edit Order</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form role="form" method="post" action="" name="productform">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Customer Name</label>
                            <input type="text" class="form-control" id="exampleInputEmail1" value="<?php echo $customer_name; ?>" name="txtcustomer" required>
                        </div>
                    </div>
                    <!-- /.col-md-6 -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Select Date: </label>
                            <div id="datepicker" class="input-group date" data-date-format="yyyy-mm-dd">
                                <input data-date-format="yyyy-mm-dd" value="<?php echo $order_date; ?>" name="orderdate" class="form-control" type="text"/>
                                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-md-12">
                        <div style="overflow-x:auto;margin:30px 0;">
                            <table style="width:1130px;" id="producttable" class="table table-striped">
                                <thead>
                                    <tr>
                                          <th>
                                            <center><button type="button" name="add" class="btn btn-info btn-sm btnadd"><span class="glyphicon glyphicon-plus"></span></button></center>
                                        </th>
                                        <th>#</th>
                                        <th>Search Product</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                        <th>Enter Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <?php 
                                
                                foreach($row_invoice_details as $item_invoice_details){
                                    $select=$pdo->prepare("select * from tbl_product where pid=".$item_invoice_details['product_id']);
                                    $select->execute();

                                    $row_product=$select->fetch(PDO::FETCH_ASSOC);
                                
                                ?>
                                <tr>
                                    <?php
                                       echo '<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove"><span class="glyphicon glyphicon-remove"></span></button></center></td>';
                                       echo '<td><input type="hidden" class="form-control pname" name="productname[]" value="'.$row_product['pname'].'" readonly></td>';
                                       echo '<td><select class="form-control productidedit" name="productid[]"><option value="">Select Option</option>'.fill_product($pdo,$item_invoice_details['product_id']).'</select></td>';
                                       echo '<td><input type="text" class="form-control stock" name="stock[]" value="'.$row_product['pstock'].'" readonly></td>';
                                       echo '<td><input type="text" class="form-control price" name="price[]" value="'.$row_product['saleprice'].'" readonly></td>';
                                       echo '<td><input type="number" min="1" class="form-control qty" name="qty[]" value="'.$item_invoice_details['qty'].'" required></td>';
                                       echo '<td><input type="text" class="form-control total" name="total[]" value="'.$row_product['total']*$item_invoice_details['qty'].'" readonly></td>';  
                                }
                                    ?>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div> <!-- For Table -->
                <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                          <label>Subtotal</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                <i class="fas fa-dollar-sign"></i>
                                </div>
                              <input type="text" value="<?php echo $subtotal;?>" class="form-control" name="txtsubtotal" id="txtsubtotal" required readonly>
                            </div>
                        </div>
                        <div class="form-group">
                          <label>Tax (5%)</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                <i class="fas fa-dollar-sign"></i>

                                </div>
                              <input type="text"  value="<?php echo $tax;?>" class="form-control" name="txttax" id="txttax" required readonly>
                            </div>
                        </div>
                        <div class="form-group">
                          <label>Discount</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                <i class="fas fa-tags"></i>
                                </div>
                              <input type="text"  value="<?php echo $discount;?>" class="form-control" name="txtdiscount" id="txtdiscount" required>
                            </div>
                        </div>
                    </div>
                    <!-- /.col-md-6 -->
                    <div class="col-md-6">
                    <div class="form-group">
                          <label>Total</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                <i class="fas fa-dollar-sign"></i>

                                </div>
                              <input type="text"  value="<?php echo $total;?>" class="form-control" name="txttotal" id="txttotal" required readonly>
                            </div>
                        </div>
                        <div class="form-group">
                          <label>Paid</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                <i class="fas fa-dollar-sign"></i>

                                </div>
                              <input type="text"  value="<?php echo $paid;?>" class="form-control" name="txtpaid" id="txtpaid" required>
                            </div>
                        </div>
                        <div class="form-group">
                          <label>Due</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                <i class="fas fa-dollar-sign"></i>

                                </div>
                              <input type="text"  value="<?php echo $due;?>" class="form-control" name="txtdue" id="txtdue" required readonly>
                            </div>
                        </div>

                        <label>Payment Method</label>
                        <div class="form-group clearfix">
                            
                      <div class="icheck-primary d-inline">
                        <input type="radio" id="radioPrimary1" value="Cash" name="rb" <?php echo ($payment_type=='Cash')?'checked':'';  ?> >
                        <label for="radioPrimary1">Cash
                        </label>
                      </div>
                      <div class="icheck-primary d-inline">
                        <input type="radio" id="radioPrimary2" value="Card" name="rb" <?php echo ($payment_type=='Card')?'checked':'';  ?>>
                        <label for="radioPrimary2">Card
                        </label>
                      </div>
                      <div class="icheck-primary d-inline">
                        <input type="radio" id="radioPrimary3" value="Check" name="rb" <?php echo ($payment_type=='Check')?'checked':'';  ?>>
                        <label for="radioPrimary3">
                          Check
                        </label>
                      </div>
                    </div>
                    </div>
                    <!-- /.col-md-6 -->
                </div>
                <hr>
                <div align="center">
                    <input type="submit" name="btnupdateorder" value="Update Order" class="btn btn-warning">
                </div>
            </form>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
    $(function() {
        $("#datepicker").datepicker({
            autoclose: true,
            todayHighlight: true
        }).datepicker('update', new Date());
    });

    //for adding data to the table
    $(document).ready(function(){

        $('.productidedit').select2()

$(".productidedit").on('change',function(e){
    var productid=this.value;
    var tr=$(this).parent().parent();
    $.ajax({
        url:'getproduct.php',
        method:'get',
        data:{
            id:productid
        },
        success:function(data){
            console.log(data);
            tr.find(".pname").val(data["pname"]);
            tr.find(".stock").val(data["pstock"]);
            tr.find(".price").val(data["saleprice"]);
            tr.find(".qty").val(1);
            tr.find(".total").val(tr.find(".qty").val() * tr.find(".price").val());
            calculate(0,0);
        }
    })
})

        $(document).on('click','.btnadd',function(){
            var html='';
            html+='<tr>';
            html+='<td><center><button type="button" name="remove" class="btn btn-danger btn-sm btnremove"><span class="glyphicon glyphicon-remove"></span></button></center></td>';
            html+='<td><input type="hidden" class="form-control pname" name="productname[]"  readonly></td>';
            html+='<td><select class="form-control productid" name="productid[]"><option value="">Select Option</option><?php echo fill_product($pdo,'');?></select></td>';
            html+='<td><input type="text" class="form-control stock" name="stock[]" readonly></td>';
            html+='<td><input type="text" class="form-control price" name="price[]" readonly></td>';
            html+='<td><input type="number" min="1" class="form-control qty" name="qty[]" required></td>';
            html+='<td><input type="text" class="form-control total" name="total[]" readonly></td>';
           
            $('#producttable').append(html);

            $('.productid').select2()

$(".productid").on('change',function(e){
    var productid=this.value;
    var tr=$(this).parent().parent();
    $.ajax({
        url:'getproduct.php',
        method:'get',
        data:{
            id:productid
        },
        success:function(data){
            console.log(data);
            tr.find(".pname").val(data["pname"]);
            tr.find(".stock").val(data["pstock"]);
            tr.find(".price").val(data["saleprice"]);
            tr.find(".qty").val(1);
            tr.find(".total").val(tr.find(".qty").val() * tr.find(".price").val());
            calculate(0,0);
            $("#txtpaid").val("");
        }
    })
})
        })
            //dynamic deletion of rows 
        $(document).on('click','.btnremove',function(){
            $(this).closest('tr').remove();
            calculate(0,0);
            $("#txtpaid").val("");
        })

        $('#producttable').delegate(".qty","keyup change", function(){
            var quantity = $(this);
            var tr=$(this).parent().parent();
            if((quantity.val()-0) > (tr.find(".stock").val()-0)){
                swal("WARNING!","Sorry! Quantity not available","warning")
                quantity.val(1);
                tr.find(".total").val(quantity.val() * tr.find(".price").val());
                calculate(0,0);
                $("#txtpaid").val("");
            }else{
                tr.find(".total").val(quantity.val() * tr.find(".price").val());
                calculate(0,0);
                $("#txtpaid").val("");
            }
        })

        function calculate(dis,paid){
            var subtotal = 0;
            var tax=0;
            var discount=dis;
            var net_total=0;
            var paid_amount=paid;
            var due=0;

            $(".total").each(function(){
                subtotal+=$(this).val()*1;
            })

            tax=0.05*subtotal;
            net_total=tax+subtotal-discount;
            due=net_total-paid_amount;

            $('#txttotal').val(net_total.toFixed(2));

            $("#txttax").val(tax.toFixed(2));

            $("#txtsubtotal").val(subtotal.toFixed(2));

            $("#txtdiscount").val(discount);
            $("#txtdue").val(due.toFixed(2))
            $("#txtpaid").val(paid_amount.toFixed(2))
        }

        $('#txtdiscount').keyup(function(){
            var discount = $(this).val();
            calculate(discount,0);
        })

        $("#txtpaid").keyup(function(){
            var paid=$(this).val();
            var discount = $("#txtdiscount").val();
            calculate(discount,paid);
        })

    })
</script>

<?php include_once 'footer.php'; ?>