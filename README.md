# Google Ad Manager Prebid LineItems Setup Tool
Automatically setup and update your Line Items on Google Ad Manager for [Prebid.js](http://prebid.org/)


## Overview
When setting up Prebid, your ad ops team often has to create [hundreds of line items](http://prebid.org/adops.html) in Google Ad Manager.

This tool automates setup for new header bidding partners, on display and video formats. You define the advertiser, placements, and Prebid settings; then, it creates an order with one line item per price level, attaches creatives, and sets placement and Prebid key-value targeting.

While this tool covers typical use cases, it might not fit your needs. Check out the [limitations](#limitations) before you dive in.

## Getting Started

### Creating Google Credentials
You will need credentials to access your Google Ad Manager account programmatically. This summarizes steps from [Google Ad Manager docs](https://developers.google.com/ad-manager/api/authentication) and the Google Ad Manager PHP library [auth guide](https://github.com/googleads/googleads-php-lib).
1. If you haven't yet, sign up for a Google Ad Manager account.
2. Create Google developer credentials
   * Go to the [Google Developers Console Credentials page](https://console.developers.google.com/apis/credentials).
   * On the **Credentials** page, select **Create credentials**, then select **Service account key**.
   * Select **New service account**, and select JSON key type. You can leave the role blank.
   * Click **Create** to download a file containing a `.json` private key.
3. Enable API access to Google Ad Manager
   * Sign into your [Google Ad Manager account](https://admanager.google.com). You must have admin rights.
   * In the **Admin** section, select **Global settings**
   * Ensure that **API access** is enabled.
   * Click the **Add a service account user** button.
     * Use the service account email for the Google developer credentials you created above.
     * Set the role to "Trafficker".
     * Click **Save**.

### Setting Up
1. Clone this repository.
2. Include the library via Composer:
`$ composer update`
3. Update customerConfigSample to meet your targets

### Verifying Setup
Let's try it out! From the top level directory, run

`php script/tests/ConnexionTest.php`

and you should whether the connexion is OK or not

## Creating Line Items

Modify the settings in 
`/script/hb/headerBiddingCreation.php`
* Require an customer Config file, such as customerConfigSample.php. This file need to include
  * Type: can be either display or video
  * NetworkId
  * An array of parameters with elements as below
    * SSP must be an array of ssp you want to create - please enter here the bidder code defined in prebid documentation
    * Price Granularity are standards, defined on [prebid.org](http://prebid.org/prebid-mobile/adops-price-granularity.html). You can also define a custom granularity by passing an array of buckets in the following format 
    
    ```
        'priceGranularity' => [ 
            'buckets' => [
                ['min' => 0, 'max' => 5, 'increment' => 0.05, 'precision' => 2 /* optional */],
                ['min' => 5, 'max' => 10, 'increment' => 0.1, 'precision' => 2 /* optional */],
                ['min' => 10, 'max' => 20, 'increment' => 0.5, 'precision' => 2 /* optional */],
            ]
        ]
    ```
    * Currency is the AdServer Currency (USD, EUR...)
    * Sizes: please enter all sizes allowed on your inventory - Not mandatory for video line items
    * GeoTargeting (not mandatory) if needed, displayed as a list of alpha-2 codes (ISO 3166)
 

Then, from the root of the repository, run:

`php script/hb/headerBiddingCreation.php`

You should be all set! Review your order, line items, and creatives to make sure they are correct. Then, approve the order in Google Ad Manager.

*Note: Google Ad Manager might show a "Needs creatives" warning on the order for ~15 minutes after order creation. Typically, the warning is incorrect and will disappear on its own.*

## Limitations
* This tool does not support additional line item targeting beyond placement, hb_bidder, and hb_pb values. Placement targeting is currently required, and targeting by ad unit isn't supported
* This tool does not modify existing orders or line items, it only creates them. If you need to make a change to an order, it's easiest to archive the existing order and recreate it. However, once orders are created, you can easily update them (change Price Granularity, change Available Sizes)
