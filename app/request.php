<?php
require __DIR__ . '/vendor/autoload.php';

$settings = require( __DIR__ . "/settings.php" );

use Asika\Pdf2text;
use Orhanerday\OpenAi\OpenAi;
use League\CommonMark\CommonMarkConverter;

// header( "Content-Type: application/json" );

$openai = new OpenAi( $settings['api_key'] );
$context = json_decode( $_POST['context'] ?? "[]" ) ?: [];

// Set up a mapping of file names to file paths for the PDF files
// that you want to use for training your chatbot
$pdf_files = array(
    'RISQ.pdf' => 'docs/RISQ.pdf',
    'file2.pdf' => 'path/to/file2.pdf',
);

// Loop through the PDF files, extract text, and concatenate the results
$training_data = '';
foreach ($pdf_files as $file_name => $file_path) {
    $pdf_text = extract_text_from_pdf($file_path);
    $training_data .= $pdf_text . "\n";
}

$c_file = curl_file_create(__DIR__ . 'files/sample_file_1.jsonl');
$result = $open_ai->uploadFile([
    "purpose" => "answers",
    "file" => $c_file,
]);

$messages = [];

if( ! empty( $settings['system_message'] ) ) {
    $messages[] = [
        "role" => "system",
        "content" => $settings['system_message'],
    ];
}

foreach( $context as $msg ) {
    $messages[] = [
        "role" => "user",
        "content" => $msg[0],
    ];
    $messages[] = [
        "role" => "assistant",
        "content" => $msg[1],
    ];
}

$messages[] = [
    "role" => "user",
    "content" => $_POST['message'],
];
    
// create a new completion
$complete = json_decode( $openai->chat( [
    'model' => 'gpt-3.5-turbo',
    'messages' => $messages,
    'temperature' => 1.0,
    'max_tokens' => 2000,
    'frequency_penalty' => 0,
    'presence_penalty' => 0
    ] ) );

// get message text
if( isset( $complete->choices[0]->message->content ) ) {
    $text = str_replace( "\\n", "\n", $complete->choices[0]->message->content );
} elseif( isset( $complete->error->message ) ) {
    $text = $complete->error->message;
} else {
    $text = "Sorry, ask Google for that question";
}

// convert markdown to HTML
$converter = new CommonMarkConverter();
$styled = $converter->convert( $text );

// return response
echo json_encode( [
    "message" => (string)$styled,
    "raw_message" => $text,
    "status" => "success",
] );


// Define a function to extract text from a PDF file
function extract_text_from_pdf($pdf_path) {
    // Use the pdf2text library to extract text from the PDF file
    $pdf = new Pdf2text();
    return $pdf->decode($pdf_path);
}



?>