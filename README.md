# Switro Crypto Payments | Magento 2

====================================

## Overview
This Magento 2 module integrates the **Solana/ETH** payments into your store's checkout process.
It provides an admin configuration panel, a frontend checkout card, and a redirect handler for payment processing.


## Features
- ⚡ **Instant Solana Transfers** – Direct on-chain settlement.  
- 🔒 **Non-Custodial** – Funds go straight to your wallet.  
- 💱 **Optional USDC Conversion** – Reduce volatility risk.  
- 🌐 **Webhook Support** – Real-time payment status updates.  


# Installation:
1. Copy module folder to your `magento_root_folder/app/code`.
2. Go to your `magento_root_folder`. Run these commands:
    - `php ./bin/magento   setup:upgrade`
    - `php ./bin/magento   cache:flush`
    - `php ./bin/magento   setup:static-content:deploy`


# Configuration:
1. **Get your API Key** [Login to your Switro account](https://switro.com/login) → **API Settings** → **Copy API Key**.
2. Go to **Admin Panel** → `Stores > Configuration > Sales > Payment Methods`.
3. Locate **Switro Solana/ETH Wallet**.
4. Enable the payment method and configure required settings (title, API keys, etc.).
5. Register webhook URL shown in admin config to your Switro dashboard


## References
- **Get Started with Switro:** [https://www.switro.com/auth/register](https://www.switro.com/auth/register)  
- **Switro API & Developer Docs:** [https://switro.com/docs](https://switro.com/docs)