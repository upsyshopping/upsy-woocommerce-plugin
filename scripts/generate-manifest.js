const fs = require('fs');

// Extract version from command line arguments
const version = process.argv[2];

const manifest = {
    "name": "Upsy Shopping Helper",
    "slug": "upsy",
    "author": "<a href='https://github.com/upsyshopping'>Upsy</a>",
    "author_profile": "https://upsyshopping.com",
    "version": version,
    "download_url": `https://github.com/upsyshopping/upsy-woocommerce-plugin/releases/download/${version}/upsy.zip`,
    "requires": "4.4.0",
    "tested": "5.8.1",
    "added": "2023-06-05 02:10:00",
    "last_updated": new Date().toISOString(),
    "homepage": "https://upsyshopping.com",
    "sections": {
      "description": `Upsy is an ai shopping assistant for SME webshops. It is designed for mobile shoppers, offering smart navigation, AI recommendations, promotions, automated customer service, and insights that will help webshops grow online sales smartly.

Turn your webshop visitors into buyers!`,
      "installation": "To install the update, simply use your automatic WordPress plugin updater.", // Alternatively, you can download the latest release from here to update manually:",
      //"changelog": "Your changelog"
    },
    "banners": {
    //   "low": "https://yourwebsite.com/images/upsy-banner-772x250.webp",
    //   "high": "https://yourwebsite.com/images/upsy-banner-1544x500.webp"
    }
};

fs.writeFileSync('manifest.json', JSON.stringify(manifest, null, 2));
