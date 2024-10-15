<?php
declare(strict_types=1);

require 'vendor/autoload.php';

// Import necessary OpenTelemetry classes
use OpenTelemetry\Contrib\Otlp\SpanExporter; 
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Common\Attribute\Attributes;

// OTLP endpoint
$endpoint = 'http://localhost:4317'; 

try {
    // Create the exporter
    $exporter = new SpanExporter(
        (new OtlpHttpTransportFactory())->create($endpoint, 'application/x-protobuf')
    );

    // Create a resource with the service name
    $attributes = Attributes::create([
        'service.name' => 'example-php' // Set the service name here
    ]);
    $resource = ResourceInfo::create($attributes);

    // Create the tracer provider with the resource
    $tracerProvider = (new TracerProviderFactory())->create($exporter, $resource);

    // Get the tracer
    $tracer = $tracerProvider->getTracer('hi');

    // Start the HTML output
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hello World in PHP 8.1</title>
    </head>
    <body>
        <h1><?php echo "Hello, World!"; ?></h1>
        <p><?php echo "Welcome to PHP 8.1!"; ?></p>
        <p>This is a basic PHP script that displays messages using PHP 8.1 features.</p>
    </body>
    </html>

    <?php
    // Start a span
    $span = $tracer->spanBuilder('example-span')->startSpan();
    $span->setAttribute('http.method', 'GET');

    // Finish the span
    $span->end();

    // Export the span
    // We need to ensure the exporter is called after the span is ended.
    $tracerProvider->shutdown(); // This should export the finished spans automatically.

    // Log the result of exporting
    error_log('Successfully exported span.');

} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
}
