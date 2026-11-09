<?php
// รายการตารางที่อนุญาตให้จัดการผ่านหน้า Back Office
$modules = [
  'products' => [
    'title'=>'สินค้า','table'=>'products','pk'=>['id_pd'],
    'fields'=>[
      'id_pd'=>['label'=>'รหัสสินค้า','type'=>'number','required'=>true],
      'ctgr_id'=>['label'=>'หมวดหมู่','type'=>'select','source'=>'categories','source_key'=>'id_categories','source_label'=>'name_categories','required'=>true],
      'pd_name'=>['label'=>'ชื่อสินค้า','type'=>'text','required'=>true],
      'pd_image_url'=>['label'=>'URL รูปสินค้า','type'=>'url'],
      'price'=>['label'=>'ราคา (บาท)','type'=>'number','step'=>'0.01','required'=>true],
    ],
    'list'=>['id_pd','pd_image_url','pd_name','ctgr_id','price']
  ],
  'categories' => [
    'title'=>'หมวดหมู่','table'=>'categories','pk'=>['id_categories'],
    'fields'=>[
      'id_categories'=>['label'=>'รหัสหมวดหมู่','type'=>'number','required'=>true],
      'name_categories'=>['label'=>'ชื่อหมวดหมู่','type'=>'text','required'=>true],
    ],
    'list'=>['id_categories','name_categories']
  ],
  'customers' => [
    'title'=>'ลูกค้า','table'=>'customers','pk'=>['ID'],
    'fields'=>[
      'ID'=>['label'=>'รหัสลูกค้า','type'=>'number','required'=>true],
      'first_name'=>['label'=>'ชื่อ','type'=>'text','required'=>true],
      'last_name'=>['label'=>'นามสกุล','type'=>'text','required'=>true],
      'email'=>['label'=>'อีเมล','type'=>'email','required'=>true],
      'phone'=>['label'=>'เบอร์โทรศัพท์','type'=>'text'],
      'address'=>['label'=>'ที่อยู่','type'=>'textarea'],
    ],
    'list'=>['ID','first_name','last_name','email','phone','address']
  ],
  'orders' => [
    'title'=>'คำสั่งซื้อ','table'=>'orders','pk'=>['order_id'],
    'fields'=>[
      'order_id'=>['label'=>'รหัสออเดอร์','type'=>'number','required'=>true],
      'user_id'=>['label'=>'ลูกค้า','type'=>'select','source'=>'customers','source_key'=>'ID','source_label'=>'email','required'=>true],
      'order_date'=>['label'=>'วันที่สั่งซื้อ','type'=>'datetime-local','required'=>true],
      'total_amount'=>['label'=>'ยอดรวม','type'=>'number','step'=>'0.01','required'=>true],
      'status'=>['label'=>'สถานะ','type'=>'select-static','options'=>['Pending'=>'Pending','Processing'=>'Processing','Completed'=>'Completed','Cancelled'=>'Cancelled'],'required'=>true],
    ],
    'list'=>['order_id','user_id','order_date','total_amount','status']
  ],
  'order_items' => [
    'title'=>'รายการสินค้าในออเดอร์','table'=>'order_items','pk'=>['order_item_id'],
    'fields'=>[
      'order_id'=>['label'=>'ออเดอร์','type'=>'select','source'=>'orders','source_key'=>'order_id','source_label'=>'order_id','required'=>true],
      'product_id'=>['label'=>'สินค้า','type'=>'select','source'=>'products','source_key'=>'id_pd','source_label'=>'pd_name','required'=>true],
      'qty'=>['label'=>'จำนวน','type'=>'number','required'=>true],
      'price'=>['label'=>'ราคาต่อชิ้น','type'=>'number','step'=>'0.01','required'=>true],
      'subtotal'=>['label'=>'ราคารวม','type'=>'number','step'=>'0.01','required'=>true],
    ],
    'list'=>['order_item_id','order_id','product_id','qty','price','subtotal']
  ],
  'product_variants' => [
    'title'=>'ขนาดสินค้า','table'=>'product_variants','pk'=>['pd_id','size_id'],
    'fields'=>[
      'pd_id'=>['label'=>'สินค้า','type'=>'select','source'=>'products','source_key'=>'id_pd','source_label'=>'pd_name','required'=>true],
      'size_id'=>['label'=>'ขนาด','type'=>'select-static','options'=>['S'=>'S','M'=>'M','L'=>'L','XL'=>'XL'],'required'=>true],
    ],
    'list'=>['_row_number','pd_id','size_id']
  ],
];
