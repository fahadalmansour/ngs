"""Connector exports for omnichannel sync."""

from .salla_connector import SallaConnector
from .shopify_connector import ShopifyConnector
from .woo_connector import WooConnector
from .zid_connector import ZidConnector

CONNECTOR_MAP = {
    "woo": WooConnector,
    "zid": ZidConnector,
    "salla": SallaConnector,
    "shopify": ShopifyConnector,
}
