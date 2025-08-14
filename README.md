# Switro Crypto Payments | Magento 2

## Overview

This Magento 2 module integrates the **Solana/ETH** payments into your store's checkout process.
It provides an admin configuration panel, a frontend checkout card, and a redirect handler for payment processing.


## Features

- ⚡ **Instant Solana Transfers** – Direct on-chain settlement.  
- 🔒 **Non-Custodial** – Funds go straight to your wallet.  
- 💱 **Optional USDC Conversion** – Reduce volatility risk.  
- 🌐 **Webhook Support** – Real-time payment status updates.  

---

## Installation

1. Copy module folder to your `magento_root_folder/app/code`.
2. Go to your `magento_root_folder`. Run these commands:
    - `php ./bin/magento   setup:upgrade`
    - `php ./bin/magento   cache:flush`
    - `php ./bin/magento   setup:static-content:deploy`


## Configuration

1. **Get your API Key**  
   [Login to your Switro account](https://www.switro.com/auth/login) → **API Settings** → **Copy API Key**.

2. **Open Magento Admin Panel & Locate Payment Method**  
   Go to `Stores > Configuration > Sales > Payment Methods` and find **Switro Solana/ETH Wallet** in the list.

3. **Enter API Key**  
   Paste the copied API Key into the **API Key** field, and then enable the payment method.

4. **Get Webhook URL**  
   Copy the Webhook URL shown in `Payments > Switro Solana Wallet`.

5. **Set Webhook in Switro Dashboard**  
   Go to [Switro Dashboard](https://www.switro.com/app/settings#api-settings) → **API Settings** → Paste the Webhook URL → **Save Webhook**.

---

## References

- **Get Started with Switro:** [https://www.switro.com/auth/register](https://www.switro.com/auth/register)  
- **Switro API & Developer Docs:** [https://switro.com/docs/get-started](https://switro.com/docs/get-started)


## Connect with us

- **X:** [https://x.com/switropay](https://x.com/switropay)  
- **Youtube:** [https://www.youtube.com/@switropay](https://www.youtube.com/@switropay)
- **Discord:** [https://discord.com/invite/switro](https://discord.com/invite/switro)