<?php

/*
|--------------------------------------------------------------------------
| Demo catalogue
|--------------------------------------------------------------------------
|
| Every title, author and description below is invented for the Book Planet
| demo store. Prices are integer cents; `sale` is the sale price in cents.
|
*/

return [

    'categories' => [
        'literary-fiction' => ['Literary Fiction', 'Character-driven novels with an ear for language — stories that stay with you long after the last page.'],
        'mystery' => ['Mystery & Suspense', 'Locked rooms, quiet villages and detectives who notice everything. Puzzles to read by lamplight.'],
        'science-fiction' => ['Science Fiction', 'Generation ships, dimming suns and machines that listen: big ideas told at a human scale.'],
        'fantasy' => ['Fantasy', 'Enchanted forests, sky cities and bargains with ferrymen. Worlds built with care, and a little danger.'],
        'history' => ['History & Biography', 'Forgotten lives and quiet revolutions, drawn from letters, ledgers and logbooks.'],
        'poetry' => ['Poetry', 'Short, luminous collections for commutes, late nights and long walks.'],
        'nature-travel' => ['Nature & Travel', 'Slow roads, night trains and hedgerows: writing that pays attention to places.'],
        'essays' => ['Essays & Ideas', 'Curious, generous non-fiction about attention, time and the way we live now.'],
    ],

    'authors' => [
        'mara-ellwood' => ['Mara Ellwood', 'Mara Ellwood grew up on the tidal flats of the east coast and worked for twenty years as a surveyor before publishing her first novel. She writes about coastlines, inheritance and the maps we make of our own lives.'],
        'tobias-wren' => ['Tobias Wren', 'Tobias Wren is a former parish bell-ringer and crossword setter whose Inspector Rosa Kell mysteries have been praised for their fair-play puzzles and wintry atmosphere.'],
        'ines-calloway' => ['Ines Calloway', 'Ines Calloway trained as a radio astronomer and spent two seasons at a polar research station. Her science fiction is known for its precision and its tenderness.'],
        'dorian-vale' => ['Dorian Vale', 'Dorian Vale writes wry, humane fiction and essays from a flat above a bookshop. He is fond of out-of-season hotels, second chances and footnotes.'],
        'priya-lindqvist' => ['Priya Lindqvist', 'Priya Lindqvist is an essayist and former librarian who writes about attention, time and the slow pleasures of reading. She lives by a canal with too many notebooks.'],
        'oskar-brandt' => ['Oskar Brandt', 'Oskar Brandt is a historian of everyday things — timetables, stamps, lighthouses — and the people who kept them running. He has spent more time in county archives than is strictly healthy.'],
        'juniper-hale' => ['Juniper Hale', 'Juniper Hale is a poet and walking guide. Her collections move between kitchens, estuaries and the long patience of hills.'],
        'soren-adeyemi' => ['Soren Adeyemi', 'Soren Adeyemi writes fantasy full of tinkers, thieves and cities that should not exist. Before writing full-time he restored antique clocks.'],
        'clementine-rook' => ['Clementine Rook', 'Clementine Rook sets her cosy but sharp-edged mysteries in villages where everyone knows everyone — and nobody knows quite enough.'],
        'hugo-marchetti' => ['Hugo Marchetti', 'Hugo Marchetti is a naturalist and travel writer who prefers buses to planes and footpaths to both. He keeps a list of every bird he has ever heard but not seen.'],
        'aurelia-stone' => ['Aurelia Stone', 'Aurelia Stone is the author of the Thornwood Cycle. She lives at the edge of an old forest and insists that it is only occasionally listening.'],
        'nadia-farrow' => ['Nadia Farrow', 'Nadia Farrow writes literary and speculative fiction about water, weather and families under pressure. Her novels have been translated into eleven languages.'],
    ],

    // [title, author, category, price_cents, sale_cents|null, pages, featured, description]
    'books' => [
        ['The Salt Cartographer', 'mara-ellwood', 'literary-fiction', 1499, null, 312, true,
            'A retired mapmaker returns to the tidal island of her childhood to chart a coastline that changes with every storm. As the maps multiply, so do the versions of the past she thought she understood — and the one summer she has spent forty years trying not to redraw.'],
        ['Lanterns on Harrow Street', 'mara-ellwood', 'literary-fiction', 1299, 899, 288, false,
            'Across one winter in a northern mill town, four neighbours keep a vigil for a missing boy and discover how little they knew about each other. A tender, unsentimental novel about the light people carry for one another.'],
        ['What the River Kept', 'nadia-farrow', 'literary-fiction', 1599, null, 356, true,
            'When a drought exposes the drowned village beneath a reservoir, a hydrologist and her estranged father walk its streets for the first time in forty years. A novel about memory, water and what we choose to let resurface.'],
        ['A Small Hotel in Autumn', 'dorian-vale', 'literary-fiction', 1199, null, 244, false,
            'The last guests of a seaside hotel\'s final season — a widower, a runaway bride and a night porter secretly writing a novel — circle each other through seven quiet days. Wry, generous and full of weather.'],
        ['The Glasshouse Year', 'mara-ellwood', 'literary-fiction', 0, null, 96, false,
            'A botanist inherits her grandmother\'s derelict glasshouse and a ledger of plants that were never meant to survive an English winter. A luminous novella, free to read — the perfect introduction to Mara Ellwood\'s fiction.'],
        ['Harvest Moon over Calder', 'nadia-farrow', 'literary-fiction', 1399, null, 330, false,
            'Three generations of a farming family gather for one final harvest before the land is sold. Over a single September week, old debts come due and a long-buried promise is finally kept.'],

        ['The Ninth Bell', 'tobias-wren', 'mystery', 1399, null, 304, true,
            'A bell-ringer is found dead in the tower of St Aldric\'s on the one night all eight bells were silent. Detective Inspector Rosa Kell has until the Sunday service to work out who rang the ninth.'],
        ['Murder at Low Tide', 'tobias-wren', 'mystery', 1299, 799, 276, false,
            'The causeway to Merrow Island floods twice a day, and the only people who could have reached the lighthouse keeper were all on the wrong side of the water. Or so they say. The second Inspector Kell mystery.'],
        ['Seven Keys to Blackwater House', 'tobias-wren', 'mystery', 999, null, 232, false,
            'A locked-house puzzle for long evenings: seven heirs, seven keys and one will that can only be read when every door in Blackwater House has been opened.'],
        ['A Quiet Kind of Poison', 'clementine-rook', 'mystery', 1499, null, 298, false,
            'In a village famous for its gardens, the prize-winning horticulturist is dying slowly, and she is sure she knows why. A retired pharmacist agrees to help her prove it before the summer show.'],
        ['The Archivist\'s Alibi', 'clementine-rook', 'mystery', 1399, null, 284, false,
            'Every document in the county archive has a provenance — except the letter that places archivist Theo March at the scene of a crime he swears he never saw.'],
        ['The Vanishing Choir', 'clementine-rook', 'mystery', 1299, null, 262, false,
            'One by one, the members of a village choir stop turning up to rehearsal. The choirmaster thinks it is stage fright. The vicar suspects something far worse.'],

        ['Orbitals', 'ines-calloway', 'science-fiction', 1699, null, 412, true,
            'Aboard a generation ship that has forgotten it is moving, a maintenance engineer finds a window — and a view that contradicts everything her people believe. Big ideas, told at a human scale.'],
        ['The Long Quiet of Europa', 'ines-calloway', 'science-fiction', 1499, 999, 368, false,
            'Twelve scientists wintering beneath the ice of Jupiter\'s moon receive a signal they cannot explain, and a message from Earth they cannot answer.'],
        ['Borrowed Suns', 'nadia-farrow', 'science-fiction', 1299, null, 322, false,
            'In a near future where daylight is rationed across a dimming Europe, a solar auditor uncovers a black market in sunshine — and a conspiracy reaching up to the orbital mirrors.'],
        ['Signal & Static', 'ines-calloway', 'science-fiction', 1199, null, 248, false,
            'Ten linked stories about machines that listen: an AI grief counsellor, a radio telescope with a crush and a satellite that refuses to come down.'],
        ['Starlight for Beginners', 'ines-calloway', 'science-fiction', 0, null, 64, false,
            'On a mining colony without a sky, a girl draws the constellations from memory for a younger sister who has never seen them. A short, hopeful novelette — free to read.'],

        ['The Thornwood Crown', 'aurelia-stone', 'fantasy', 1799, null, 486, true,
            'Book one of the Thornwood Cycle. A hedge-witch\'s apprentice is crowned by a forest that has never chosen a queen before — and has never let one leave.'],
        ['Ashes of the Thornwood', 'aurelia-stone', 'fantasy', 1799, null, 512, false,
            'Book two of the Thornwood Cycle. War reaches the forest\'s edge, and the young queen must decide what she is willing to let burn to keep the old wood alive.'],
        ['Moth & Ember', 'aurelia-stone', 'fantasy', 1199, null, 212, false,
            'Six fireside tales from the world of the Thornwood, including the first meeting of the moth-queen and the ember-knight.'],
        ['The Tinker of Hollowmere', 'soren-adeyemi', 'fantasy', 1399, 899, 344, false,
            'A travelling tinker who mends broken spells for a living is hired to repair the one enchantment holding the city of Hollowmere in the sky.'],
        ['The Ferryman\'s Bargain', 'soren-adeyemi', 'fantasy', 1299, null, 296, false,
            'Every soul who crosses the grey river pays the ferryman a single memory. One passenger offers him a memory of his own.'],
        ['Cinder Harbour', 'soren-adeyemi', 'fantasy', 1599, null, 402, false,
            'A dockside thief, a stolen dragon\'s egg and a harbour city ruled by guilds of fire — all in the space of one very long night.'],

        ['The Lighthouse Keepers', 'oskar-brandt', 'history', 1899, null, 384, true,
            'A history of the men and women who kept the coastal lights burning, drawn from logbooks, letters and the stories their children told.'],
        ['Paper Empires', 'oskar-brandt', 'history', 1699, 1199, 352, false,
            'How the humble ledger, the postage stamp and the printed railway timetable quietly built the modern world.'],
        ['The Map Room', 'oskar-brandt', 'history', 1499, null, 296, false,
            'Six maps that changed history, and the overlooked people — surveyors, engravers, a teenage apprentice — who drew them.'],
        ['Winter of the Printers', 'oskar-brandt', 'history', 1399, null, 268, false,
            'The true-to-life story of a small press that kept printing forbidden poetry through the hardest winter of the century.'],
        ['Edith Marlowe: A Life in Margins', 'oskar-brandt', 'history', 1599, null, 336, false,
            'The first full biography of the botanical illustrator whose uncredited drawings filled a century of field guides.'],

        ['Weather Report for the Heart', 'juniper-hale', 'poetry', 999, null, 88, true,
            'Short, luminous poems about ordinary days: kettles, commuter trains and the particular blue of four o\'clock in February.'],
        ['Field Notes', 'juniper-hale', 'poetry', 899, null, 76, false,
            'Poems written on foot — hedgerows, estuaries and the long patience of hills.'],
        ['Small Hours', 'juniper-hale', 'poetry', 799, 499, 64, false,
            'A sequence of night poems for insomniacs, new parents and anyone awake while the rest of the world sleeps.'],
        ['The Tidal Book of Hours', 'juniper-hale', 'poetry', 1099, null, 112, false,
            'A year of devotional poems keyed to the tides of a single bay, from the spring floods to the still water of midwinter.'],

        ['Slow Roads through Umbria', 'hugo-marchetti', 'nature-travel', 1399, null, 288, false,
            'On foot and by rattling country bus through hill towns and olive harvests, and a patient education in the art of arriving late.'],
        ['The Hedgerow Year', 'hugo-marchetti', 'nature-travel', 1299, null, 256, false,
            'A naturalist\'s month-by-month guide to the most overlooked habitat in the countryside, with notes on what to listen for.'],
        ['Night Trains', 'hugo-marchetti', 'nature-travel', 1499, 999, 318, false,
            'Sleeper journeys across twelve countries, and the strangers met between departure and dawn.'],
        ['Birdsong at the Edge of the City', 'hugo-marchetti', 'nature-travel', 1199, null, 224, false,
            'Listening for nightingales, swifts and one stubborn wren in the margins of a modern city.'],

        ['The Art of Paying Attention', 'priya-lindqvist', 'essays', 1399, null, 248, true,
            'Essays on focus, boredom and wonder in an age designed to distract — and a practical case for looking at one thing for a long time.'],
        ['Borrowed Time', 'priya-lindqvist', 'essays', 1299, null, 232, false,
            'Essays on clocks, calendars and why every culture has invented its own way of being late.'],
        ['On Reading Slowly', 'priya-lindqvist', 'essays', 999, 699, 144, false,
            'A short, persuasive argument for reading fewer books more deeply, with a reading list you will never quite finish.'],
        ['The Kindness Ledger', 'dorian-vale', 'essays', 1199, null, 208, false,
            'Stories of small generosities, and the surprisingly rigorous science of why they matter more than we think.'],
    ],

    // Drafts are visible in the admin area only.
    'drafts' => [
        ['The Thornwood Throne', 'aurelia-stone', 'fantasy', 1899, null, 520, false,
            'Book three of the Thornwood Cycle. Coming soon.'],
    ],

    'reviews' => [
        5 => [
            'Read it in two evenings and I keep thinking about it. Beautifully paced, and the ending earns every page.',
            'The writing is gorgeous — I kept stopping to reread sentences out loud to whoever was nearby.',
            'Exactly the kind of book I hoped to find here. Thoughtful, precise and never showy.',
            'Quietly brilliant. It made my commute the best part of the day for a whole week.',
            'Smart and generous. I will be reading everything else this author has written.',
            'Bought it on a whim during the sale and it has become my favourite read of the year.',
        ],
        4 => [
            'A slow start, but by the halfway point I could not put it down.',
            'Warm, clever and a little strange in the best way. Already recommended it to three friends.',
            'Lovely, careful writing and a real sense of place. The file looked great on my tablet, too.',
            'Really enjoyed this. One or two threads felt rushed, but the characters stayed with me.',
        ],
        3 => [
            'Not entirely for me, but I can see why people love it — the craft is undeniable.',
            'Solid and enjoyable, though I wanted a little more from the final third.',
        ],
    ],

];
