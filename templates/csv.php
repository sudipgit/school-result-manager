<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students_sample.csv');

// Output the CSV headers
$output = fopen('php://output', 'w');
fputcsv($output, [
    'name','name_en','roll','class_no','section','section_id','gender','group','father_name','father_name_en','mother_name','mother_name_en','birth_reg_no','dob','district','upozilla','union_name','village','address','religion'
]);

// Sample data
$students = [
    ['রিমা রহমান','Rima Rahman',1,6,'A',1,'Female','Science','আব্দুল রহমান','Abdul Rahman','সেলিনা রহমান','Selina Rahman','BRN001','2010-05-10','Satkhira','Kaligonj','Ratanpur','Village1','Address1','Islam'],
    ['সোহেল করিম','Sohel Karim',2,6,'A',1,'Male','Commerce','মোঃ করিম','Mohammad Karim','সেলিমা করিম','Selima Karim','BRN002','2010-06-15','Rajshahi','Paba','Harian','Village2','Address2','Hindu'],
    ['আনিকা হাসান','Anika Hasan',3,7,'A',2,'Female','Humanities','মোঃ হাসান','Mohammad Hasan','ফাতেমা হাসান','Fatema Hasan','BRN003','2011-03-20','Satkhira','Shyamnagar','Kushura','Village3','Address3','Islam'],
    ['রিফাতুল ইসলাম','Rifatul Islam',4,7,'A',2,'Male','Science','আনিসুল ইসলাম','Anisul Islam','রাবেয়া ইসলাম','Rabeya Islam','BRN004','2011-09-12','Rajshahi','Godagari','Deopara','Village4','Address4','Hindu']
];

foreach ($students as $student) {
    fputcsv($output, $student);
}

fclose($output);
exit;
