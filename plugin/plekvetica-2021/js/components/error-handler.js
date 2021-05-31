/**
 * Error Handling Object
 */
 var plekerror = {
    display_error(field, message){
        console.log(field);
        console.log(message);
        toastr.error(field,message);
    },
    display_info(field, message){
        console.log(field);
        console.log(message);
        toastr.info(field,message);
    }
}