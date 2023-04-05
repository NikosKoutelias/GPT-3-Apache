const message_input = document.querySelector('button')
const message_list = document.querySelector( "#chat-messages" );
const question_asked = document.getElementById( "input_question" );

const context = [];

message_input.addEventListener( "click", function( e ) {

        add_message( "outgoing", question_asked.value );
        send_message();

} );

function send_message() {
    let question = question_asked.value;
    let message = add_message( "incoming", '<div id="cursor"></div>' );
     question_asked.value = "";
     
    fetch( "request.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "message=" + encodeURIComponent( question ) + "&context=" + encodeURIComponent( JSON.stringify( context ) )
    } )
    .then( response => response.text() )
    .then( data => {
        const json = JSON.parse( data );
        if( json.status == "success" ) {
            update_message( message, json.message );
            context.push([question, json.raw_message]);
        }
        question_asked.focus();
    } );
}

function add_message( direction, message ) {
    const message_item = document.createElement( "div" );
    message_item.classList.add( "chat-message" );
    message_item.classList.add( direction+"-message" );
    message_item.innerHTML = '<p>' + message + "</p>";
    message_list.appendChild( message_item );
    message_list.scrollTop = message_list.scrollHeight;
    hljs.highlightAll();
    return message_item;
}

function update_message( message, new_message ) {
    message.innerHTML = '<p>' + new_message + "</p>";
    message_list.scrollTop = message_list.scrollHeight;
    hljs.highlightAll();
}