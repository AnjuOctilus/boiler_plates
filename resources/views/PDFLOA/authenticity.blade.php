<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>CLAIM LION LAW</title>
        <style>
         @page {
         margin: 1cm 2cm;
         padding:0cm 0cm;
         }
         /** Define now the real margins of every page in the PDF **/
         body {
         margin:0cm;
         font-family: sans-serif;   
         letter-spacing: 0px;      
        }
        table tbody{
         width: 100%;
         }
         /** Define the footer rules **/
         footer {
         position: fixed; 
         bottom: 0cm; 
         left:-2cm; 
         right:-2cm;
         height: 2cm;
         line-height: 1;
         }
       
     
      </style>
   </head>

   <body>
      <table style="width:100%;"> 
         <tbody > 


            <tr > 
                <td style="width:100%; text-align:center; "> 
                   <h1 style="font-weight:400; font-size:29px;"> 
                       <i> Certificate of Authenticity </i>
                   </h1>
                </td>
            </tr>
            <tr> 
                <td style="width:100%;"> 
                   <div style="border:solid 0px black; border-bottom:1px;"> 
                   <p style="font-size:13px; text-align:center; padding:0px 20px; font-weight:400;">
                        <i> 
                          The purpose of this Certificate of Authenticity is to attest that the following client has digitally signed the
                           relevant documentation using the details below
                        </i>
                    </p>
                   </div>
                </td>
            </tr>
            <tr> 
                <td> 
                    <table style="width:64%;"> 
                        <tbody> 
                            <tr> 
                                 <td style="padding-top:24px;"> 
                                     <b>Document Created</b>
                                </td>
                                <td style="padding-top:24px; padding-left:30px;"> 
                                     <span> {{$current_date}}</span>
                                </td>
                            </tr>
                            <tr> 
                                 <td style="padding-top:28px;"> 
                                    <b>Client </b>
                                </td>
                                <td style="padding-top:28px; padding-left:30px;"> 
                                     <span>{{$user_data->first_name . " " . $user_data->last_name}}</span>
                                </td>
                            </tr>
                            <tr> 
                                 <td style="padding-top:28px;"> 
                                    <b>Document Signed </b>
                                </td>
                                <td style="padding-top:28px; padding-left:30px;"> 
                                     <span>{{$user_data->signature_created_at}}</span>
                                </td>
                            </tr>
                            <tr> 
                                 <td style="padding-top:28px;"> 
                                    <b>Document Reference </b>
                                </td>
                                <td style="padding-top:28px; padding-left:30px;"> 
                                     <span>{{$refNo}} </span>
                                </td>
                            </tr>
                            <tr> 
                                 <td style="padding-top:28px;"> 
                                    <b> IP Address </b>
                                </td>
                                <td style="padding-top:28px; padding-left:30px;"> 
                                     <span>{{$user_data->ip_address}}</span>
                                </td>
                            </tr>
                            <tr> 
                                 <td style=""> 
                                    <b>Signature </b>
                                </td>
                                <td style=" padding-left:30px;"> 
                                     <span>
                                        <div style="max-width:100%; max-height:200px;"> 
                                        <img  style="height:40px;" src="data:image/png;base64,{{$user_data->signature_image}}" />  
                                        </div>
                                     </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    
                    <tr> 
                        <td> 
                            <footer style="border-top:solid 1px black; margin: 1cm 2cm 11cm 2cm; font-size:11px; font-weight:400; text-align:center;"> 
                                <p> 
                               <i> 
                                  ClaimLion Law is a trading style of BlackLion Law LLP, a limited liability partnership registered in England & Wales, authorised and
                                    regulated by the Solicitors Regulation Authority under number 518911. A list of the members of the LLP is displayed at our
                                    registered office at BlackLion Law LLP, Berkeley Square House, Berkeley Square, London W1J 6BD.
                               </i>   
                             </p>
                            </footer>
                        </td>
                    </tr>
                </td>
            </tr>
         </tbody>
      </table>
   </body>

</html>