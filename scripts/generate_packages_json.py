#!/usr/bin/env python3
"""Generate content/packages/*.json from September 2026 webmaster texts. Prices stay XX."""

from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "content" / "packages"
DRAFT = ROOT / "content" / "draft"
OUT.mkdir(parents=True, exist_ok=True)
DRAFT.mkdir(parents=True, exist_ok=True)

# Homepage bestsellers from Portfolio PDF: 1.1, 2.1, 2.2, 3.1
BESTSELLERS = {
    "bolaven-plateau-classic-full-day-tour": 100,
    "bolaven-plateau-classic-2-day": 95,
    "4000islands-vatphu-temple-2-day-1-night": 90,
    "3-day-classic-experience-in-southern-laos": 85,
}

PACKAGES = [
    {
        "code": "1.1",
        "slug": "bolaven-plateau-classic-full-day-tour",
        "title": "Bolaven Plateau Classic Full-Day Tour",
        "headline": "Cool highlands, dramatic waterfalls and the real taste of Lao coffee",
        "duration": "1 day",
        "duration_term": "1-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard. Comfort upgrades available.",
        "dream": "Leave the heat of Pakse behind and climb into the cool, green highlands of the Bolaven Plateau. In one day you stand above Laos’ highest waterfalls, walk through a lively highland market, and taste coffee roasted by people who grow it themselves. This is the essential Bolaven day — private, flexible, and built around nature, villages and coffee.",
        "highlights": [
            "Tad Fane — the tallest twin waterfall in Laos (optional zipline)",
            "Tad Yuang — multi-tier falls with viewpoints and swimming possibilities",
            "Highland market and everyday village life",
            "Seed-to-cup coffee tasting with a local expert",
            "Private air-conditioned vehicle throughout",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Pakse – Bolaven Plateau – Pakse",
                "body": "Departure: 08:00 from your hotel in Pakse. The road climbs quickly from the Mekong plain into a cooler climate of coffee gardens, pine trees and mist. First stop: Tad Champee. Continue to Tad Fane (about 120 m) and Tad Yuang (about 40 m). Stop at Lak 40 Café, Ban Kok Phung Tai Market, then the Tad Lo area and Ngae community. End with coffee tasting with Mr. Vieng before returning to Pakse. Vehicle journey: approximately 220 km. Meals: not included.",
            }
        ],
        "included": [
            "Private minivan and driver",
            "All entrance fees mentioned in the program",
            "Bottled water during the tour",
        ],
        "excluded": [
            "English-speaking guide (available on request)",
            "Lunch and extra meals",
            "Tips, travel insurance, personal expenses",
            "Optional zipline at Tad Fane",
        ],
        "upgrades": [
            "Qualified English- or French-speaking guide",
            "Lunch in a local restaurant",
            "Zipline at Tad Fane",
        ],
        "destinations": ["bolaven-plateau"],
        "image": "tad-fane.jpg",
        "cta": "Request this tour on WhatsApp • Get a personalized quote for your dates",
    },
    {
        "code": "1.2",
        "slug": "vatphu-riverside-gems-full-day",
        "title": "Vat Phou & Riverside Gems Full-Day",
        "headline": "A UNESCO temple, a royal town and a quiet return on the Mekong",
        "duration": "1 day",
        "duration_term": "1-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard. Comfort upgrades and guide available.",
        "dream": "Vat Phou is not a quick photo stop. This day gives time for the museum, the long sacred causeway and the climb to the holy spring — then Champasak town and a two-hour boat back to Pakse on the Mekong. The most complete cultural day trip from Pakse.",
        "highlights": [
            "Full visit of Vat Phou UNESCO World Heritage Site",
            "Hong Nang Sida (12th-century Khmer sanctuary)",
            "Walk in Champasak, former royal town",
            "Return to Pakse by private boat (about 2 hours)",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Pakse – Vat Phou – Champasak – Mekong – Pakse",
                "body": "Morning at the Vat Phou complex: museum, electric shuttle, ancient causeway and sacred spring. Short drive to Hong Nang Sida. Walk through Champasak. Return to Pakse by boat (about two hours). Vehicle journey: approximately 90 km + boat return. Meals: not included.",
            }
        ],
        "included": [
            "Private minivan and boat",
            "All entrance fees mentioned in the program",
            "Bottled water",
        ],
        "excluded": [
            "Guide (on request), lunch, tips, insurance, personal expenses",
        ],
        "upgrades": [
            "Qualified guide (English or French) — strongly recommended for Vat Phou",
            "Lunch in Champasak",
            "Extra time in Champasak town or at the temple",
        ],
        "destinations": ["champasak"],
        "image": "vatphou.jpg",
        "cta": "Book this Vat Phou day • Add a guide for the history of the temple",
    },
    {
        "code": "1.3",
        "slug": "4000islands-full-day",
        "title": "4000 Islands Full-Day",
        "headline": "Li Phi, island life and Khone Phapheng — the Mekong in one day",
        "duration": "1 day",
        "duration_term": "1-day",
        "departure": "07:30 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard. Comfort upgrades available.",
        "dream": "Leave Pakse in the morning and spend the day on the Mekong islands. Walk the old French bridge, stand above Li Phi, then finish at Khone Phapheng — the largest waterfall in Southeast Asia by volume. A full Southern Laos classic without an overnight stay.",
        "highlights": [
            "Boat crossing to Don Khone",
            "French colonial bridge and old locomotive",
            "Li Phi (Somphamit) waterfall + optional zipline",
            "Khonepasoi waterfall",
            "Khone Phapheng, the “Niagara of the East”",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Pakse – 4,000 Islands – Pakse",
                "body": "Departure: 07:30 from Pakse. Drive to Ban Nakasang, boat to Don Khone. French colonial bridge, Li Phi Waterfall, Khonepasoi, then Khone Phapheng. Return to Pakse around 18:30. Vehicle journey: approximately 324 km. Meals: not included.",
            }
        ],
        "included": [
            "Private minivan and driver",
            "Local transport in the 4000 Islands",
            "All entrance fees mentioned in the program",
            "Bottled water",
        ],
        "excluded": [
            "Guide (available on request)",
            "Lunch and extra meals",
            "Tips, insurance, personal expenses, optional zipline",
        ],
        "upgrades": [
            "Qualified guide (English or French)",
            "Lunch at a local restaurant on the islands",
            "Zipline at Li Phi",
        ],
        "destinations": ["4000-islands"],
        "image": "liphi.jpg",
        "cta": "Request this full-day island tour • WhatsApp us for your date",
    },
    {
        "code": "1.4",
        "slug": "pakse-cultural-riverside-exploration-full-day",
        "title": "Pakse Cultural & Riverside Exploration – Full-Day",
        "headline": "Islands, artisans, markets and the Golden Buddha above the Mekong",
        "duration": "1 day",
        "duration_term": "1-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard. Guide available on request.",
        "dream": "A full day inside and around Pakse: a boat on the Mekong, weaving and stone-carving villages, the confluence of two rivers, the main market and sunset from Wat Phu Salao. Ideal for travellers who arrive or leave the same day and still want a real sense of the town.",
        "highlights": [
            "Boat to Don Kho Island",
            "Ban Saphai weaving and Buddha carving village",
            "Confluence of the Xe Don and the Mekong",
            "Pakse Museum and Dao Heuang Market",
            "Golden Buddha / Wat Phu Salao viewpoint",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Pakse and the Mekong",
                "body": "Departure: 08:00. Boat to Don Kho, Ban Saphai handicrafts, Wat Chomphet, Bung Udom and Pun Tao Kong Shrine at the confluence. Afternoon: Pakse Museum, Dao Heuang Market, Wat Phu Salao Golden Buddha. Vehicle/boat journey: approximately 45 km. Meals: not included.",
            }
        ],
        "included": [
            "Private transport by boat and tuk-tuk",
            "All entrance fees mentioned in the program",
            "Bottled water",
        ],
        "excluded": [
            "Guide (available on request)",
            "Lunch and extra meals",
            "Tips, insurance, personal expenses",
        ],
        "upgrades": [
            "Qualified guide (English or French)",
            "Lunch in a local restaurant",
            "Extra time at the Golden Buddha for sunset",
        ],
        "destinations": ["pakse"],
        "image": "donkhone.jpg",
        "cta": "Book this Pakse day • Add a guide if you want more history and context",
    },
    {
        "code": "1.5",
        "slug": "dan-sinxai-eco-adventure-trek-full-day",
        "title": "Dan Sinxai Eco Adventure Trek Full-Day",
        "headline": "A 4-hour highland trek, hidden waterfalls and Tad Fane",
        "duration": "1 day",
        "duration_term": "1-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard. Comfort upgrades available.",
        "dream": "This is not the classic waterfall loop by car. You walk about 7 km through coffee farms, pine forest and jungle to Dan Sinxai, swim or picnic at Tad Champee, then finish at Tad Yuang and Tad Fane. For travellers who want the Bolaven with their feet on the trail.",
        "highlights": [
            "About 4 hours / 7 km trek from Ban Lak 38",
            "Dan Sinxai: forest, rocks, Buddha statues, hidden falls",
            "Picnic lunch at Tad Champee",
            "Tea and coffee tasting at Mea Boua",
            "Tad Yuang and Tad Fane to close the day",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Pakse – Dan Sinxai Trek – Tad Fane – Pakse",
                "body": "Departure: 08:00. At Ban Lak 38 meet a local guide for a ~7 km trek to Dan Sinxai. Continue to Tad Champee for picnic lunch, then Tad Yuang, Mea Boua Tea Farm and Tad Fane. Vehicle journey: approximately 128 km. Meals: picnic lunch included.",
            }
        ],
        "included": [
            "Private minivan and driver",
            "Local guide during the trek",
            "Picnic lunch at Tad Champee",
            "Entrance fees mentioned + bottled water",
        ],
        "excluded": [
            "English-speaking guide for the whole day (available on request)",
            "Extra meals, tips, insurance, personal expenses",
        ],
        "upgrades": [
            "Qualified English- or French-speaking guide for the full day",
            "Zipline at Tad Fane",
            "Swimming time at Tad Champee or Tad Yuang",
        ],
        "destinations": ["bolaven-plateau"],
        "image": "tad-champee.jpg",
        "cta": "Request this eco-trek • Tell us your fitness level before we confirm",
    },
    {
        "code": "1.6",
        "slug": "champasak-cycling-experience",
        "title": "Champasak Cycling Experience",
        "headline": "Rice fields, villages and Vat Phou at your own pace",
        "duration": "½–1 day",
        "duration_term": "1-day",
        "departure": "09:00 from Ban Muang Saen",
        "price_from": "XX",
        "price_note": "From $XX per person — four route options. Support vehicle available.",
        "dream": "The quiet roads around Champasak are made for a bicycle. Ride through rice fields and villages to UNESCO-listed Vat Phou, with the option to continue to Wat Muang Kang. Four routes — from a short active ride to a longer day with a support vehicle.",
        "highlights": [
            "Mountain bike + local guide",
            "Countryside paths and Mekong views",
            "Vat Phou temple and museum",
            "Optional extension to Wat Muang Kang",
            "Support vehicle on two of the four options",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Four flexible routes",
                "body": "Depart 09:00 from the bike shop in Ban Muang Saen. Option 1: 100% cycling to Vat Phou and back. Option 2: cycling + songthaew return. Option 3: cycling + Wat Muang Kang. Option 4: cycling + songthaew + Wat Muang Kang. Meals: not included. Transfer from Pakse not included unless requested.",
            }
        ],
        "included": [
            "Mountain bike rental",
            "Local guide with basic English",
            "Support vehicle on Options 2 and 4",
        ],
        "excluded": [
            "Transfer from / to Pakse (can be added)",
            "Lunch, extra water, tips, insurance, personal expenses",
            "Higher-level English- or French-speaking guide (on request)",
        ],
        "upgrades": [
            "Private transfer from Pakse and back",
            "Qualified English- or French-speaking guide",
            "Lunch in Champasak or near Vat Phou",
            "Choice of the longer route (Options 3 or 4)",
        ],
        "destinations": ["champasak"],
        "image": "champasak.jpg",
        "cta": "Choose your cycling option • WhatsApp us your fitness level and starting point",
    },
    {
        "code": "1.7",
        "slug": "full-day-trekking-at-dan-yai-tiger-falls",
        "title": "Full-Day Trekking at Dan Yai & Tiger Falls",
        "headline": "Coffee, pine forest, a 1,290 m viewpoint and a swimming waterfall",
        "duration": "1 day",
        "duration_term": "1-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard. Hard-trek option available.",
        "dream": "Start with coffee at Jhai, walk through Ban Nong Luang, then trek into pine forest and jungle to Dan Yai at 1,290 metres. Picnic in the trees, then swim or rest at the Head of Tiger Waterfall. A highland day for walkers, not a road-only waterfall tour.",
        "highlights": [
            "Jhai Coffee House and Ban Nong Luang village",
            "Trek through coffee farms, pine forest and jungle",
            "Dan Yai viewpoint at 1,290 m",
            "Picnic in nature",
            "Head of Tiger Waterfall — swim or rest",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Pakse – Ban Nong Luang – Dan Yai – Tiger Falls – Pakse",
                "body": "Departure: 08:00. Jhai Coffee House, Ban Nong Luang, trek to Dan Yai viewpoint, picnic, optional hard trek to lower falls, Head of Tiger Waterfall, return to Pakse. Vehicle journey: approximately 95 km.",
            }
        ],
        "included": [
            "Private minivan and driver",
            "Local guide during the trek",
            "Entrance fees mentioned + bottled water",
        ],
        "excluded": [
            "English-speaking guide for the whole day (on request)",
            "Lunch unless confirmed in the quote",
            "Tips, insurance, personal expenses",
        ],
        "upgrades": [
            "Qualified English- or French-speaking guide",
            "Picnic lunch included in the package",
            "Hard trek to the lower waterfalls (fit walkers only)",
        ],
        "destinations": ["bolaven-plateau"],
        "image": "waterfall.jpg",
        "cta": "Request this highland trek • Tell us if you want the easy trail or the hard option",
    },
    {
        "code": "2.1",
        "slug": "bolaven-plateau-classic-2-day",
        "title": "Bolaven Plateau Classic – 2 Days / 1 Night",
        "headline": "Waterfalls, coffee culture, ethnic villages and a night in the cool highlands",
        "duration": "2 days / 1 night",
        "duration_term": "2-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard accommodation. Comfort upgrades available.",
        "dream": "Spend two days in the cool climate and rich culture of the Bolaven Plateau. Discover powerful waterfalls, highland markets and traditional villages, then wake up in the heart of Laos’ coffee country — far from the crowds of the main circuit. A private vehicle keeps the pace flexible.",
        "highlights": [
            "Tad Fane and Tad Yuang waterfalls (optional zipline)",
            "Overnight on the plateau in a peaceful 3-star setting",
            "Thateng Market and Katu weaving village",
            "Tad Lo area and Ngae community traditions",
            "Coffee tasting with a local specialist",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Pakse to the Bolaven Plateau",
                "body": "Departure: 08:00. Tad Champee, Tad Fane, Tad Yuang, Jhai Coffee House. Overnight at a 3-star resort on the plateau. Meals: Lunch / Dinner. Vehicle: ~100 km.",
            },
            {
                "day": 2,
                "title": "Highland markets, villages, coffee and return to Pakse",
                "body": "Thateng Market, Ban Kandone weaving village, Ban Kok Phung Tai Market, Tad Lo / Ngae community, Mr. Vieng coffee tasting, return to Pakse. Meals: Breakfast / Lunch. Total distance: about 340 km.",
            },
        ],
        "included": [
            "Private minivan and driver",
            "1 night accommodation (standard / 3-star)",
            "Meals as mentioned (Day 1: lunch + dinner / Day 2: breakfast + lunch)",
            "All entrance fees mentioned + bottled water",
        ],
        "excluded": [
            "English-speaking guide (available on request)",
            "Extra meals, tips, insurance, personal expenses",
            "Optional zipline at Tad Fane",
        ],
        "upgrades": [
            "Qualified guide (English or French)",
            "Superior / comfort hotel on the plateau",
            "Zipline at Tad Fane",
        ],
        "destinations": ["bolaven-plateau"],
        "image": "bolaven.jpg",
        "cta": "Book this 2-day experience • Chat with us on WhatsApp",
    },
    {
        "code": "2.2",
        "slug": "4000islands-vatphu-temple-2-day-1-night",
        "title": "4,000 Islands & Vat Phou Temple – 2 Days / 1 Night",
        "headline": "River life, giant waterfalls and a UNESCO temple",
        "duration": "2 days / 1 night",
        "duration_term": "2-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard accommodation. Comfort upgrades available.",
        "dream": "One night on the Mekong islands, then Vat Phou on the way back to Pakse. This short journey combines the slow rhythm of Si Phan Don with the spiritual weight of a Khmer temple older than Angkor Wat. Ideal when time is limited but you still want the two signature experiences of the south.",
        "highlights": [
            "Overnight on Don Khone",
            "Li Phi waterfall, French bridge and old locomotive",
            "Khone Phapheng — the “Niagara of the East”",
            "Full visit of Vat Phou UNESCO site",
            "Walk in Champasak town",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Pakse to the 4,000 Islands",
                "body": "Ban Nakasang boat to Don Khone. French bridge, Li Phi, Khonepasoi, Ban Hang Khone. Overnight on Don Khone. Meals: Lunch / Dinner. Vehicle: ~152 km.",
            },
            {
                "day": 2,
                "title": "Khone Phapheng, Vat Phou, Champasak, Pakse",
                "body": "Khone Phapheng, ferry to Champasak, Vat Phou full visit, Champasak walk, return to Pakse. Meals: Breakfast / Lunch. Total distance: about 343 km.",
            },
        ],
        "included": [
            "Private minivan and driver + local transport in the 4000 Islands",
            "1 night accommodation",
            "Meals as mentioned + entrance fees + bottled water",
        ],
        "excluded": [
            "Guide (on request), extra meals, tips, insurance, personal expenses, optional zipline",
        ],
        "upgrades": [
            "Qualified guide (English or French)",
            "Superior / comfort hotel on Don Khone",
            "Zipline at Li Phi",
            "Private boat instead of shared local boat, if available",
        ],
        "destinations": ["4000-islands", "champasak"],
        "image": "khone.jpg",
        "cta": "Request this 2-day combination • WhatsApp us for dates and upgrades",
    },
    {
        "code": "2.3",
        "slug": "vatphu-champasak-discovery-2days-1night",
        "title": "Vat Phu & Champasak Discovery – 2 Days / 1 Night",
        "headline": "Khmer temples, village crafts and a night on Don Daeng",
        "duration": "2 days / 1 night",
        "duration_term": "2-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard accommodation. Comfort upgrades available.",
        "dream": "Two days focused on Champasak rather than the 4000 Islands. You walk to Nang Sida, visit Vat Phou, see bamboo and pottery workshops, sleep on Don Daeng in the Mekong, then continue to Tomo and Phou Asa. The cultural counterpart to the island packages.",
        "highlights": [
            "Countryside walk to Hong Thao Tao and Nang Sida",
            "Full visit of Vat Phou UNESCO site",
            "Bamboo and pottery workshops",
            "Overnight on Don Daeng + optional Tak Bat",
            "Tomo temple and Phou Asa viewpoint",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Ancient temples and Don Daeng",
                "body": "Transfer to Champasak. Walk to Hong Thao Tao and Nang Sida. Vat Phou, bamboo and pottery workshops, ferry to Don Daeng overnight. Meals: Lunch / Dinner. Vehicle: ~58 km.",
            },
            {
                "day": 2,
                "title": "Tomo, Phou Asa and return to Pakse",
                "body": "Optional Tak Bat. Tomo Temple, Ban Kiet Ngong, Phou Asa viewpoint, Lak 14 Market, return to Pakse. Meals: Breakfast / Lunch. Total distance: about 178 km.",
            },
        ],
        "included": [
            "Private minivan and driver",
            "1 night accommodation on Don Daeng",
            "Meals as mentioned + entrance fees + bottled water",
        ],
        "excluded": [
            "English-speaking guide (on request)",
            "Extra meals, tips, insurance, personal expenses",
        ],
        "upgrades": [
            "Qualified guide (English or French)",
            "Superior room on Don Daeng if available",
            "Extra time at Vat Phou or Phou Asa",
        ],
        "destinations": ["champasak"],
        "image": "hero-temple.jpg",
        "cta": "Book this Champasak discovery • Ask for a guide if you want the temple stories in full",
    },
    {
        "code": "3.1",
        "slug": "3-day-classic-experience-in-southern-laos",
        "title": "3-Day Classic Experience in Southern Laos",
        "headline": "4000 Islands + Vat Phou + Bolaven Plateau — the perfect introduction",
        "duration": "3 days / 2 nights",
        "duration_term": "3-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard accommodation. Comfort upgrades available.",
        "dream": "In three days you experience the three icons of Southern Laos: the quiet river life of the 4000 Islands, the power of Khone Phapheng, the spiritual atmosphere of UNESCO-listed Vat Phou, and the cool green highlands of the Bolaven Plateau. A private vehicle and a clear rhythm make this the flagship introduction to the region.",
        "highlights": [
            "Overnight on Don Khone in the 4000 Islands",
            "Li Phi and Khone Phapheng waterfalls",
            "Full visit of Vat Phou (UNESCO World Heritage)",
            "Full day on the Bolaven Plateau: waterfalls and coffee",
            "Completely private journey",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Pakse to the 4,000 Islands",
                "body": "Don Khone: French bridge, Li Phi, Khonepasoi, Ban Hang Khone. Overnight Don Khone. Meals: Lunch / Dinner.",
            },
            {
                "day": 2,
                "title": "Khone Phapheng, Vat Phou and return to Pakse",
                "body": "Khone Phapheng, Vat Phou full visit, Champasak walk, overnight Pakse. Meals: Breakfast / Lunch / Dinner.",
            },
            {
                "day": 3,
                "title": "Bolaven Plateau and return to Pakse",
                "body": "Tad Champee, Tad Fane, Tad Yuang, Lak 40 Café, markets, Tad Lo / Ngae, Mr. Vieng coffee, return Pakse. Meals: Breakfast / Lunch. Total distance: about 529 km.",
            },
        ],
        "included": [
            "Private minivan and driver",
            "2 nights accommodation (standard / 3-star)",
            "Meals as mentioned + entrance fees + bottled water",
        ],
        "excluded": [
            "English-speaking guide (available on request)",
            "Extra meals, tips, insurance, personal expenses",
            "Optional activities (zipline, etc.)",
        ],
        "upgrades": [
            "Qualified guide (English or French)",
            "Superior hotels on Don Khone and in Pakse",
            "Zipline at Li Phi or Tad Fane",
        ],
        "destinations": ["bolaven-plateau", "4000-islands", "champasak"],
        "image": "hero-islands.jpg",
        "cta": "Start this classic journey • Request dates and price on WhatsApp",
    },
    {
        "code": "3.2",
        "slug": "3-day-highlights-of-southern-laos",
        "title": "3-Day Highlights of Southern Laos",
        "headline": "Bolaven, Vat Phou and the 4000 Islands — the short complete circuit",
        "duration": "3 days / 2 nights",
        "duration_term": "3-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard accommodation. Comfort upgrades available.",
        "dream": "Three days for travellers with little time who still want the three pillars of the south: the Bolaven Plateau with Phu Krateh, Vat Phou and Champasak, then a night on Don Khone with Li Phi and Khone Phapheng. Faster than the 3-Day Classic, with a volcano viewpoint instead of a full Bolaven market day.",
        "highlights": [
            "Tad Fane, Tad Yuang and Jhai Coffee",
            "Phu Krateh volcano summit (1,300 m)",
            "Vat Phou + Champasak + Tomo",
            "Overnight on Don Khone",
            "Li Phi, French remains and Khone Phapheng",
        ],
        "itinerary": [
            {
                "day": 1,
                "title": "Bolaven Plateau and Phu Krateh",
                "body": "Tad Fane, Tad Yuang, Jhai Coffee, Phu Krateh summit (~1,300 m), overnight Pakse. Meals: Lunch / Dinner.",
            },
            {
                "day": 2,
                "title": "Vat Phou, Tomo and the 4,000 Islands",
                "body": "Vat Phou, Champasak, Tomo temple, boat to Don Khone overnight. Meals: Breakfast / Lunch / Dinner.",
            },
            {
                "day": 3,
                "title": "Islands, Khone Phapheng, Pakse or border",
                "body": "Li Phi, Khonepasoi, Khone Phapheng, then Pakse or Nong Nok Khiene border. Meals: Breakfast / Lunch. Total distance: about 505 km.",
            },
        ],
        "included": [
            "Private minivan and driver + local island transport",
            "2 nights accommodation",
            "Meals as mentioned + entrance fees + bottled water",
        ],
        "excluded": [
            "Guide (on request), extra meals, tips, insurance, personal expenses",
        ],
        "upgrades": [
            "Qualified guide (English or French)",
            "Superior hotels in Pakse and on Don Khone",
            "Zipline at Tad Fane or Li Phi",
            "End at the Cambodia border instead of Pakse",
        ],
        "destinations": ["bolaven-plateau", "4000-islands", "champasak"],
        "image": "coffee.jpg",
        "cta": "Request these 3-day highlights • Confirm whether you finish in Pakse or at the border",
    },
    {
        "code": "4.1",
        "slug": "4-day-southern-laos-escape",
        "title": "4-Day Southern Laos Escape",
        "headline": "Bolaven, Vat Phou, hidden temples and the 4000 Islands",
        "duration": "4 days / 3 nights",
        "duration_term": "4-6-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard accommodation. Comfort upgrades available.",
        "dream": "Four days is the sweet spot for most travellers: waterfalls and coffee on the Bolaven Plateau, a full cultural day around Champasak and Vat Phou, then the islands of the Mekong. The last day can finish in Pakse or at the Cambodia border — useful for travellers continuing south.",
        "highlights": [
            "Bolaven waterfalls + Jhai Coffee + Phu Krateh volcano viewpoint",
            "Boat to Champasak, rice-field walk, Nang Sida and Vat Phou",
            "Tomo temple, Ban Nong Bueng and Phou Asa viewpoint",
            "Overnight on Don Khone + Li Phi and Khone Phapheng",
            "Optional transfer to the Laos–Cambodia border",
        ],
        "itinerary": [
            {"day": 1, "title": "Pakse – Bolaven Plateau – Pakse", "body": "Tad Fane, Tad Yuang, Jhai Coffee, Phu Krateh, overnight Pakse. Meals: Lunch / Dinner."},
            {"day": 2, "title": "Pakse to Champasak (boat + Vat Phou)", "body": "Boat to Champasak, Hong Thao Tao, Nang Sida, Vat Phou, overnight Champasak. Meals: Breakfast / Lunch / Dinner."},
            {"day": 3, "title": "Champasak to the 4,000 Islands", "body": "Tomo, Ban Nong Bueng, Phou Asa, boat via Don Som to Don Khone. Meals: Breakfast / Lunch / Dinner."},
            {"day": 4, "title": "4,000 Islands then border or Pakse", "body": "Li Phi, Khonepasoi, Khone Phapheng, then border or Pakse. Meals: Breakfast / Lunch. Total distance: about 456 km."},
        ],
        "included": [
            "Private minivan and driver + local transport in the 4000 Islands",
            "3 nights accommodation",
            "Meals as mentioned + entrance fees + bottled water",
        ],
        "excluded": [
            "Guide (on request), extra meals, tips, insurance, personal expenses, optional theatre or activities",
        ],
        "upgrades": [
            "Qualified guide (English or French)",
            "Superior / comfort hotels in Pakse, Champasak and on Don Khone",
            "Palateu shadow puppet theatre in Champasak (seasonal)",
            "Zipline at Tad Fane or Li Phi",
            "End the tour at the Cambodia border instead of Pakse",
        ],
        "destinations": ["bolaven-plateau", "4000-islands", "champasak"],
        "image": "fisherman.jpg",
        "cta": "Plan this 4-day escape • Tell us if you finish in Pakse or at the Cambodia border",
    },
    {
        "code": "5.1",
        "slug": "5-day-exploring-southern-laos",
        "title": "5-Day Exploring Southern Laos",
        "headline": "The complete south: plateau, temples and two nights on the Mekong",
        "duration": "5 days / 4 nights",
        "duration_term": "4-6-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard accommodation. Comfort upgrades available.",
        "dream": "Five days give the region room to breathe. After the Bolaven and Champasak, you spend two nights in the 4000 Islands instead of rushing through them. The last morning is Khone Phapheng, then either Pakse or the Cambodia border.",
        "highlights": [
            "Full Bolaven day with waterfalls, coffee and Phu Krateh",
            "Champasak by boat + Vat Phou + Nang Sida",
            "Tomo, Phou Asa and a Mekong cruise to Don Khone",
            "Full island day: Li Phi, colonial remains, Don Loppadi",
            "Two nights on the islands + flexible end point",
        ],
        "itinerary": [
            {"day": 1, "title": "Bolaven Plateau", "body": "Tad Fane, Tad Yuang, Jhai Coffee, Phu Krateh, overnight Pakse. Meals: Lunch / Dinner."},
            {"day": 2, "title": "Pakse to Champasak", "body": "Boat, Nang Sida, Vat Phou, Champasak overnight. Meals: Breakfast / Lunch / Dinner."},
            {"day": 3, "title": "Champasak to the 4,000 Islands", "body": "Tomo, Phou Asa, boat via Don Som to Don Khone. Meals: Breakfast / Lunch / Dinner."},
            {"day": 4, "title": "Full day in the 4,000 Islands", "body": "Li Phi, Khonepasoi, French remains, Don Loppadi, overnight Don Khone. Meals: Breakfast / Lunch / Dinner."},
            {"day": 5, "title": "Khone Phapheng then border or Pakse", "body": "Khone Phapheng, then Nong Nok Khiene border or Pakse. Meals: Breakfast / Lunch."},
        ],
        "included": [
            "Private transportation + local island transport",
            "4 nights accommodation + meals as mentioned + entrance fees + water",
        ],
        "excluded": [
            "Guide (on request), extra meals, tips, insurance, personal expenses, optional activities",
        ],
        "upgrades": [
            "Qualified guide (English or French)",
            "Superior / comfort hotels in Pakse, Champasak and on Don Khone",
            "Palateu shadow puppet theatre in Champasak (seasonal)",
            "Zipline at Tad Fane or Li Phi",
            "End the tour at the Cambodia border instead of Pakse",
        ],
        "destinations": ["bolaven-plateau", "4000-islands", "champasak"],
        "image": "sunset.jpg",
        "cta": "Request this 5-day journey • Confirm your end point: Pakse or Cambodia border",
    },
    {
        "code": "6.1",
        "slug": "6-day-journey-to-the-heart-of-southern-laos",
        "title": "6-Day Journey to the Heart of Southern Laos",
        "headline": "Bolaven, Vat Phou, cycling, a Baci homestay and the 4000 Islands",
        "duration": "6 days / 5 nights",
        "duration_term": "4-6-day",
        "departure": "08:00 from Pakse",
        "price_from": "XX",
        "price_note": "From $XX per person — Standard accommodation. Comfort upgrades available.",
        "dream": "The longest published journey: waterfalls and coffee, Champasak and Vat Phou, a cycling day through villages, a Baci ceremony and homestay on Don Daeng, then two days in the 4000 Islands. Built for travellers who want culture and nature at a human pace.",
        "highlights": [
            "Bolaven waterfalls, Jhai Coffee and Phu Krateh",
            "Vat Phou + Nang Sida by boat from Pakse",
            "Cycling day: pottery, weaving, Wat Muang Kang",
            "Baci ceremony and homestay on Don Daeng",
            "Two days in the 4000 Islands",
        ],
        "itinerary": [
            {"day": 1, "title": "Bolaven Plateau and return to Pakse", "body": "Tad Fane, Tad Yuang, Jhai Coffee, Phu Krateh, overnight Pakse. Meals: Lunch / Dinner."},
            {"day": 2, "title": "Boat to Champasak and Vat Phou", "body": "Boat, Nang Sida, Vat Phou, overnight Champasak. Meals: Breakfast / Lunch / Dinner."},
            {"day": 3, "title": "Cycling in Champasak and homestay on Don Daeng", "body": "Cycling workshops and villages, boat to Don Daeng, Baci ceremony and homestay. Meals: Breakfast / Lunch / Dinner."},
            {"day": 4, "title": "Don Daeng to the 4,000 Islands", "body": "Optional Tak Bat, Tomo, Phou Asa, boat to Don Khone. Meals: Breakfast / Lunch / Dinner."},
            {"day": 5, "title": "Full day in the 4,000 Islands", "body": "Li Phi, Khonepasoi, French remains, Don Loppadi, overnight Don Khone. Meals: Breakfast / Lunch / Dinner."},
            {"day": 6, "title": "Khone Phapheng then Pakse or the Cambodia border", "body": "Khone Phapheng, then Pakse or Nong Nok Khiene border. Meals: Breakfast / Lunch."},
        ],
        "included": [
            "Private transportation + local island / cycling support as in the program",
            "5 nights (Pakse, Champasak, Don Daeng homestay, 2 nights Don Khone)",
            "Meals as mentioned + entrance fees + bottled water",
        ],
        "excluded": [
            "Full-time English-speaking guide (on request)",
            "Extra meals, tips, insurance, personal expenses, optional theatre or zipline",
        ],
        "upgrades": [
            "Qualified guide (English or French) for all or part of the journey",
            "Superior hotels instead of standard / homestay on selected nights",
            "Palateu shadow puppet theatre in Champasak (seasonal)",
            "Zipline at Tad Fane or Li Phi",
            "End at the Cambodia border instead of Pakse",
        ],
        "destinations": ["bolaven-plateau", "4000-islands", "champasak"],
        "image": "bolaven.jpg",
        "cta": "Request this 6-day journey • Tell us hotel level and your end point",
    },
]

TAILOR = {
    "code": "T.1",
    "slug": "tailor-made-tours",
    "page_type": "page",
    "title": "Tailor-made / Custom Tours",
    "headline": "Your dates, your pace, your combination of places",
    "duration": "Flexible",
    "price_from": "XX",
    "price_note": "Quoted after first contact — we start from your dates, group size and hotel level.",
    "dream": "The published packages are starting points, not limits. Families, couples, small groups and travellers with a specific interest (coffee, temples, trekking, photography, border transfers) can have a private itinerary built around their time in Pakse. One local team handles the vehicle, hotels, boats and optional guide.",
    "highlights": [
        "Any mix of Bolaven, Vat Phou, Champasak and the 4000 Islands",
        "Extra nights, slower pace, or a focus on one region only",
        "Airport / border / hotel transfers from Pakse, Ubon, Don Det, Cambodia border",
        "Guide in English or French",
        "Hotel level from standard to comfort / superior",
    ],
    "included": [],
    "excluded": [],
    "upgrades": [
        "Qualified guide (English or French)",
        "Superior hotels throughout the itinerary",
        "Special interests: coffee farms, trekking, photography, family pace",
        "Private boats and exclusive transfers",
    ],
    "destinations": ["bolaven-plateau", "4000-islands", "champasak", "pakse"],
    "image": "logo-wpt.jpg",
    "cta": "Send your dates and ideas on WhatsApp • We reply with a clear first proposal",
}

# Draft spreadsheet prices — NOT for public display
PRICES_DRAFT = {
    "note": "Internal draft from Price Braquette sheet 2026-09-01. Do not publish until client confirms. Public site uses From $XX.",
    "currency": "USD",
    "packages": {
        "3-day-classic-experience-in-southern-laos": {
            "label": "Top 1 L1 3-Day Classic",
            "twin_from_2pax": 396.95,
            "single_1pax": 700.51,
        },
        "6-day-journey-to-the-heart-of-southern-laos": {
            "label": "Top 2 L5 6 Days Heart",
            "twin_from_2pax": 990.41,
            "single_1pax": 1774.44,
        },
        "5-day-exploring-southern-laos": {
            "label": "Top 3 L4 5 Days Exploring",
            "twin_from_2pax": 801.90,
            "single_1pax": 1436.43,
        },
        "4000islands-vatphu-temple-2-day-1-night": {
            "label": "Top 4 IL2 Islands & Vat Phu 2D1N",
            "twin_from_2pax": 214.63,
            "single_1pax": 388.76,
        },
        "bolaven-plateau-classic-2-day": {
            "label": "Top 5 B5 Bolaven 2D1N",
            "twin_from_2pax": 215.02,
            "single_1pax": 367.64,
        },
        "4-day-southern-laos-escape": {
            "label": "Top 6 L3 4 Days Escape",
            "twin_from_2pax": 674.70,
            "single_1pax": 1265.23,
        },
        "3-day-highlights-of-southern-laos": {
            "label": "Top 7 L2 3-Day Highlights",
            "twin_from_2pax": 467.80,
            "single_1pax": 832.07,
        },
        "vatphu-champasak-discovery-2days-1night": {
            "label": "Top 8 W3 Vat Phu Champasak 2D1N",
            "twin_from_2pax": 199.22,
            "single_1pax": 354.57,
        },
    },
}


def main() -> None:
    for pkg in PACKAGES:
        slug = pkg["slug"]
        pkg["page_type"] = "tour"
        pkg["bestseller"] = slug in BESTSELLERS
        pkg["priority"] = BESTSELLERS.get(slug, 50 if pkg["code"].startswith("1.") else 40)
        path = OUT / f"{slug}.json"
        path.write_text(json.dumps(pkg, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
        print("wrote", path.name)

    (OUT / "tailor-made-tours.json").write_text(
        json.dumps(TAILOR, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
    )
    print("wrote tailor-made-tours.json")

    index = {
        "source": "docs/phr info for website/20261009 content info",
        "bestsellers": list(BESTSELLERS.keys()),
        "packages": [p["slug"] for p in PACKAGES] + ["tailor-made-tours"],
    }
    (OUT / "index.json").write_text(json.dumps(index, indent=2) + "\n", encoding="utf-8")

    (DRAFT / "prices_draft.json").write_text(
        json.dumps(PRICES_DRAFT, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
    )
    print("wrote content/draft/prices_draft.json (not for public display)")


if __name__ == "__main__":
    main()
