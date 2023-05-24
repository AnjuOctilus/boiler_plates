<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title></title>
      <style>
.page-break{
   @page {
         margin: 1cm;
         /* padding-top:0cm;
         padding-left: 1cm;
         padding-right: 0cm; */
         }
         /** Define now the real margins of every page in the PDF **/
         body {
         /* margin-top: 0cm;
         margin-right: 1cm;
         margin-bottom: 1cm;
         margin-left: 0cm; */
         font-family: Arial, Helvetica, sans-serif;
         }

         table{
         width: 100%;
         }
         table tbody{
         width: 100%;
         }
         .page-break {
         page-break-after: always;
         width:100%;
         margin:0px;
         padding:0px;
         }
}


      </style>
      </head>

<body>
<table style="width:100%; font-family: Arial, Helvetica, sans-serif; font-size:14px;">
         <tbody>
            <tr>
            <td>
            <table   style="width:100%; font-family: Arial, Helvetica, sans-serif; font-size:14px;">
         <tbody>
            <tr>
               <td>
               <p style="font-size:14px; padding:0px 0px; color:#45484f; margin-top:0px; text-align:right;">
         Ref -
         {{$refNo}}</p>
                  <h2 style="font-size:18px; color:#45484f; margin-bottom:5px; margin-top:30px; text-align:center;">
                  <u> STATEMENT OF TRUTH</u>
                  </h2>
                  <p style="font-size:14px; color:#45484f; margin-bottom:0px; margin-top:30px;text-align:center;">
                     <strong>I believe that the facts stated in this witness statement are true. I understand that proceedings for
contempt of court may be brought against anyone who makes, or causes to be made, a false statement
in a document verified by a statement of truth without an honest belief in its truth.  </strong>
                  </p>
                  <p style="font-size:14px; color:#45484f; margin-bottom:0px; margin-top:40px;">
                  Claimant: {{$user_data['first_name'] . " " . $user_data['last_name']}}</p>
               </td>

            </tr>  
         </tbody>
      </table>
<table style="width:100%; font-family: Arial, Helvetica, sans-serif; font-size:14px;margin-top:40px;"> 
   <tbody>  
      <tr>
         <td style="width:10%;">
            <p style="font-size:14px; color:#45484f; margin-bottom:0px;">
               Signed:</p>
         </td>

         <td align="left">
            <img style="height:40px;" src="data:image/png;base64,{{$user_data['s3_file_path']}}" />               
         </td>
      </tr>
   </tbody>
</table>

<table style="width:100%; font-family: Arial, Helvetica, sans-serif; font-size:14px;margin-top:50px;"> 
   <tbody>  
      <tr>
            <td style="font-size:14px; color:#45484f; margin-bottom:0px; width:5%;">
               <p >
               dated:</p>
            </td>
            
            <td align="left" style="margin-top:70px;">
            {{$current_date}}
               
            </td>
      </tr>
   </tbody>
</table>
</td>
            </tr>  
         </tbody>
      </table>  
      
</body>
</html>