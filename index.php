<!doctype html>
<html>
    <head>
        <title>PHP TEST BAYARIND</title>
        <!-- Datatable CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
        <script src=" https://code.jquery.com/jquery-3.7.0.js "></script>
 

        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
         

        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>


    </head>

    <body >
    <style>
    td.details-control {
                background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
                cursor: pointer;
            }

            tr.shown td.details-control {
                background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
            }
    </style>
        <div class="container" style="margin-top:30px">
            <div class="row">
                <div class="col-md-12">
                    <label> Input Nominal Belanja Ahiri Dengan ENTER</label>
                    <input type ="text"  class ="form-control" id ="nominal" name ="nominal" placeholder="Nominal Belanja"  onkeypress="return isNumber(event)"> 

                </div>  

                <div class="col-md-12 mt-5 mb-2">
                    <h6 class="text-center">KEMUNGKINAN NOMINAL YANG DIBAYARKAN</h6>

                    <div class="table-responsive">
                        <table class="table" id ="table">
                            <thead>
                                <tr>
                                    <th> </th>
                                    <th>NO</th>
                                    <th>NOMINAL</th>
                                   
                                </tr>
                            </thead>
                        </table>

                    </div>
                </div>  
                
            </div>    
        
        
        
        </div>

        
        <!-- Script -->
        <script>
            function isNumber(evt) {
                evt = (evt) ? evt : window.event;
                var charCode = (evt.which) ? evt.which : evt.keyCode;
                console.log(charCode);
                if (  charCode > 31 && (charCode < 48 || charCode > 57)) {
                        return false;
                }
                return true;

            }
        $(document).ready(function(){

            var dataNominal =[];
            var dataDetail =[];
            // var numberRenderer = $.fn.dataTable.render.number('.', ',', 2, ).display;
            var numberRenderer2 = $.fn.dataTable.render.number('.', ',', 0, ).display;
            var table =$('#table').DataTable({
                "paging": true,
                "processing": true,
                "data": dataNominal,
                'columns': [

                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "searchable":      false,
                        "data":           null,
                        "defaultContent": ''
                    },
                 
                    {data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'nominal',name:'nominal' ,className:"text-right",
                        render :function(data,type,row) {
                            if(data > 0) {
                                return numberRenderer2(data);
                            }else{
                                return '<span class ="badge badge-sm badge-warning">  UANG PAS</span>'
                            }

                        }
                    }
                ],
                createdRow: function ( row, data, index ) {
                    if (data.nominal === 0) {
                    var td = $(row).find("td:first");
                    td.removeClass( 'details-control' );
                    }
                },
            });

            $('#nominal').on('keypress', function (e) {
                if(e.which === 13){

                    $.ajax({
                        type: 'POST',
                        url: 'funct.php',
                        dataType: 'json',
                        data: { "kemungkinanNominal":$("#nominal").val()},
                        success: function (result) {
                            // console.log(result);
                        
                           table.clear();
                           table.rows.add(result)
                           table.draw(false);
                        },
                        error: function (xhr, textStatus, error) {
                            console.log(xhr);
                            console.log(textStatus);
                            console.log(error);
                        }
                    });

                }
            })
            // $('#table tbody').on('click', '.btn-detail', function () {
            $('#table tbody').on('click', 'td.details-control', function () {

             
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var jenis =row.data().jenis;
                var nominal =row.data().nominal;
                var tableId =  nominal;
         
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {

                    row.child(format(tableId)).show();
                    subTable(tableId, row.data());
                    tr.addClass('shown');
                    tr.next().find('td').addClass('no-padding');

                }

                if (jenis== 1) {
                    $.ajax({
                        type: 'POST',
                        url: 'funct.php',
                        dataType: 'json',
                        data: { "detailPecahan":nominal},
                        success: function (result) {
                            console.log(result);
                            $('#' + tableId).DataTable().clear();
                            $('#' + tableId).DataTable().rows.add(result);
                            $('#' + tableId).DataTable().draw(false);
                        },
                        error: function (xhr, textStatus, error) {
                            console.log(xhr);
                            console.log(textStatus);
                            console.log(error);
                        }
                    });

                }else{  

                    res =[{"detail":"1 lembar Rp ."+ numberRenderer2(nominal)} ];
                    console.log(res);
                    $('#' + tableId).DataTable().clear();
                    $('#' + tableId).DataTable().rows.add(res);
                    $('#' + tableId).DataTable().draw(false);
                }

            });

            function subTable(tableId, datax) {

                if (!$.fn.dataTable.isDataTable('#' + tableId)) {
                     $('#' + tableId).DataTable().clear().destroy();
                };
                $('#' + tableId).DataTable({
                    "processing": true,
                    "pageLength": 100,
                    "lengthChange": false,
                    "searching": false,
                    "info":     false,
                    "responsive":true,
                    "paging":false,
                    "destroy":true,
                    "data": dataDetail,
                    columns: [
                        {data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                        },
                        {data: 'detail'}
                    ]
                })

            }
            function format (table_id ) {
                return '<div class="row ">'+
                '<div class="col-md-12 mt-2 mb-2 text-center"><span class ="text-bold text-primary text-center">DETAIL PECAHAN ' + table_id + '</span></div><div class="table-responsive table-compact"><table id="'+table_id+'"class="table" style=" width : 70%;margin:auto;">'+
                '<thead>'+
                '<th>NO</th>'+
                '<th>PECAHAN</th>'+
                '</thead>'+
                '<tbody>'+
                '</tbody>'+
                '</table>'+
                '</div>';
            }

        });
        </script>
    </body>

</html>
