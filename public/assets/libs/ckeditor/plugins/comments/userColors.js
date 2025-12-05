/* ============================================================================
 * User Color Registry — shared across repos & plugins
 * ============================================================================
 * - Deterministic colors (stable across sessions)
 * - Unlimited unique hues
 * - Simple API: UserColors.get(userId)
 * ============================================================================
 */

var UserColors = (function () {
  'use strict';

  var USER_COLORS = {};
  var GOLDEN_RATIO_CONJUGATE = 0.618033988749895;

  // Convert HSL → HEX
  function hslToHex(h, s, l) {
    h /= 360; s /= 100; l /= 100;
    const hue2rgb = (p, q, t) => {
      if (t < 0) t += 1;
      if (t > 1) t -= 1;
      if (t < 1/6) return p + (q - p) * 6 * t;
      if (t < 1/2) return q;
      if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
      return p;
    };
    let r, g, b;
    if (s === 0) {
      r = g = b = l; // gray
    } else {
      const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
      const p = 2 * l - q;
      r = hue2rgb(p, q, h + 1/3);
      g = hue2rgb(p, q, h);
      b = hue2rgb(p, q, h - 1/3);
    }
    const toHex = x => Math.round(x * 255).toString(16).padStart(2, '0');
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
  }

  // Stable hash for a user ID string
  function hashCode(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      hash = (hash << 5) - hash + str.charCodeAt(i);
      hash |= 0;
    }
    return Math.abs(hash);
  }

  function get(userId) {
    if (!userId) return "#ffe066";
    if (USER_COLORS[userId]) return USER_COLORS[userId];

    const baseHue = (hashCode(userId) % 360);
    const hue = (baseHue * GOLDEN_RATIO_CONJUGATE * 360) % 360;
    const saturation = 70;
    const lightness = 60;
    const color = hslToHex(hue, saturation, lightness);

    USER_COLORS[userId] = color;
    return color;
  }

  return { get };
})();
