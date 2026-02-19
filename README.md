# CIPIT Dashboard Grid

**CIPIT Dashboard Grid** is a lightweight WordPress plugin designed to showcase external data visualizations (Tableau, PowerBI, etc.) using the same visual language as your site's blog. It leverages the Golden Ratio ($\phi \approx 1.618$) to ensure visual harmony and is fully adaptive to tabbed layouts and multi-column grids.

---

## 🚀 Key Features
- **Custom Post Type**: Dedicated "Dashboards" menu in the WordPress admin for easy management.
- **Dynamic Grid Logic**: Support for customizable column counts via shortcode attributes.
- **Responsive Design**: Gracefully transitions from 4 columns on desktop to a single column on mobile.
---

## 🛠 Installation

1. Clone the repository into your WordPress plugins directory:

   cd wp-content/plugins
   git clone https://github.com/Muchwat/dashboard-grid.git

2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Dashboards > Categories** to create your groups (e.g., ai, ip).
4. Add a new Dashboard, upload a thumbnail, and paste your Tableau/PowerBI link in the provided meta box.

---

## 📖 Usage

Use the following shortcode to render your grids:

### Basic AI Grid (Default 3 columns)
[dashboard-grid id="ai"]

### Compact IP Grid (4 columns)
[dashboard-grid id="ip" cols="4"]

### Full-Width Feature (1 or 2 columns)
[dashboard-grid id="featured" cols="2"]

---

## 🔧 Shortcode Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| id | (empty) | The slug of the Dashboard Category to display. |
| cols | 3 | Number of columns (Supports 1, 2, 3, or 4). |

---
