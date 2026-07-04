<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$db = Database::getInstance();
$pdo = $db->getPdo();

$categorias = $db->select("SELECT id, slug, nome FROM categorias WHERE slug IN ('cristianismo-esoterico','gnose-esoterismo','consciencia-meditacao','trabalho-interior','corpo-regeneracao','hermetismo-teosofia','filosofia-consciencia','musica-sons','frequencias-cura','historia-cultura')");

$catMap = [];
foreach ($categorias as $c) {
    $catMap[$c['slug']] = (int)$c['id'];
}

$agora = date('Y-m-d H:i:s');

function existeSlug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM artigos WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetchColumn() > 0;
}

function inserirArtigo($pdo, $catId, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora) {
    $stmt = $pdo->prepare("INSERT INTO artigos (categoria_id, autor_id, titulo, slug, resumo, conteudo, tags, fonte, status, views, publicado_em, atualizado_em, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'publicado', 0, ?, ?, ?)");
    $stmt->execute([$catId, 1, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora, $agora, $agora]);
    return $pdo->lastInsertId();
}

$criados = 0;
$ignorados = [];

if (isset($catMap['cristianismo-esoterico'])) {
    $cid = $catMap['cristianismo-esoterico'];

    $slug1 = 'jesus-gnostico-o-cristo-interior-e-a-sabedoria-oculta';
    if (!existeSlug($pdo, $slug1)) {
        $titulo1 = 'Jesus Gnóstico: O Cristo Interior e a Sabedoria Oculta';
        $resumo1 = 'O Cristianismo Esotérico revela um Jesus iniciado nos mistérios, cuja mensagem vai muito além da teologia ortodoxa. Uma exploração das tradições gnósticas e dos evangelhos de Nag Hammadi.';
        $conteudo1 = <<<'HTML'
<div class="destaque-box">
<p><strong>Cristianismo Esotérico</strong> não é uma heresia, mas a face oculta de uma mesma tradição — aquela que convida o ser humano ao despertar interior através do conhecimento direto (<em>gnosis</em>). Enquanto a ortodoxia consolidou dogmas, os gnósticos preservaram a essência iniciática dos ensinamentos de Jesus.</p>
</div>

<h2>Jesus como Iniciado</h2>
<p>A visão gnóstica de Cristo difere radicalmente da imagem construída pelos concílios do século IV. Para os gnósticos, Jesus não era um Deus distante que veio salvar a humanidade pelo sacrifício vicário, mas um <strong>iniciado</strong> — alguém que havia percorrido o caminho do autoconhecimento e alcançado a união com o Divino. Seu propósito era despertar nos outros a mesma centelha divina que existia dentro de cada um.</p>
<p>Nos textos de Nag Hammadi, encontramos um Jesus que ensina através de parábolas enigmáticas, diálogos íntimos com seus discípulos e rituais simbólicos. O <em>Evangelho de Tomé</em>, por exemplo, apresenta 114 ditos de Jesus, muitos dos quais ecoam ensinamentos das tradições de sabedoria perene — do budismo ao hermetismo.</p>

<blockquote>Jesus disse: "Se vos trouxerem à luz aquilo que está diante de vós, e disserem: 'Vinde a nós!', então o Reino se manifestará. Mas se não conhecerdes a vós mesmos, estareis na pobreza, e sereis a pobreza." — Evangelho de Tomé, dito 3</blockquote>

<h2>A Diferença Entre o Cristianismo Ortodoxo e o Esotérico</h2>
<p>O Cristianismo Ortodoxo, estabelecido a partir do Concílio de Niceia (325 d.C.), enfatiza:</p>
<ul>
<li>A salvação pela fé — crer em Jesus como salvador</li>
<li>A autoridade da Igreja e dos sacerdotes como intermediários</li>
<li>Os dogmas da Trindade, encarnação, ressurreição física</li>
<li>A Bíblia como única fonte de revelação</li>
</ul>
<p>Já o Cristianismo Esotérico (gnóstico) defende:</p>
<ul>
<li>A salvação pelo conhecimento — conhecer a si mesmo como caminho para conhecer a Deus</li>
<li>A experiência direta do Divino, sem intermediários</li>
<li>Interpretação simbólica e psicológica dos textos sagrados</li>
<li>Múltiplas revelações, incluindo evangelhos apócrifos e tradições orais</li>
</ul>
<p>Essa distinção não é meramente acadêmica. Ela toca a própria essência da <strong>Filosofia Perene</strong> — a ideia de que todas as grandes tradições religiosas compartilham um núcleo comum de verdades universais, acessível não pela crença, mas pelo trabalho interior.</p>

<h2>Os Evangelhos de Nag Hammadi</h2>
<p>Descobertos em 1945 no Egito, os 52 textos da biblioteca de Nag Hammadi revolucionaram nossa compreensão do cristianismo primitivo. Entre eles destacam-se:</p>
<ul>
<li><strong>Evangelho de Tomé</strong> — uma coleção de 114 ditos secretos de Jesus, muitos dos quais paralelos aos evangelhos canônicos, mas com um tom claramente esotérico. Enfatiza o autoconhecimento como chave para o Reino.</li>
<li><strong>Evangelho de Filipe</strong> — aborda os sacramentos gnósticos, o significado da união espiritual e a importância do matrimônio sagrado (sizígia).</li>
<li><strong>Evangelho da Verdade</strong> — um sermão poético sobre o despertar da ignorância para o conhecimento, atribuído a Valentim, o grande mestre gnóstico do século II.</li>
<li><strong>Pistis Sophia</strong> — um diálogo extenso entre Jesus ressurreto e seus discípulos, revelando a cosmologia gnóstica completa, com suas hierarquias celestiais e o drama da Alma (Sophia).</li>
</ul>

<h2>Cristo Interior e a Jornada do Despertar</h2>
<p>Para os gnósticos, o evento central não era a crucificação histórica, mas o <strong>despertar do Cristo interior</strong> em cada ser humano. Jesus veio para mostrar o caminho — não para ser adorado como ídolo, mas para ser seguido como mestre. A verdadeira salvação consistia em lembrar-se de quem realmente se é: uma centelha divina aprisionada no mundo material (<em>Kenoma</em>), que precisa encontrar o caminho de volta à Plenitude (<em>Pleroma</em>).</p>
<p>Esse processo de despertar não é automático. Exige trabalho consciente: autoconhecimento, meditação e a prática constante da <strong>auto-observação</strong>. Como ensina o Evangelho de Tomé (dito 70): "Se trouxerdes à tona o que está dentro de vós, isso vos salvará. Se não trouxerdes à tona o que está dentro de vós, isso vos destruirá."</p>

<blockquote>"O Reino do Pai está espalhado pela terra, e os homens não o veem" — Evangelho de Tomé, dito 113. A sabedoria está disponível, mas requer olhos que vejam e ouvidos que ouçam.</blockquote>
HTML;
        $tags1 = 'cristianismo esotérico,gnose,jesus gnóstico,nag hammadi,evangelhos apócrifos,gnosticismo,cristo interior';
        $fonte1 = 'Nag Hammadi Library; Evangelho de Tomé; Pistis Sophia; Elaine Pagels, "Os Evangelhos Gnósticos"';
        inserirArtigo($pdo, $cid, $titulo1, $slug1, $resumo1, $conteudo1, $tags1, $fonte1, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug1;
    }

    $slug2 = 'pistis-sophia-o-drama-cosmico-da-alma';
    if (!existeSlug($pdo, $slug2)) {
        $titulo2 = 'Pistis Sophia: O Drama Cósmico da Alma';
        $resumo2 = 'O texto gnóstico Pistis Sophia apresenta uma cosmologia fascinante onde a Alma (Sophia) cai, sofre e encontra a redenção através do conhecimento. Uma jornada que ecoa o caminho de todo buscador espiritual.';
        $conteudo2 = <<<'HTML'
<div class="destaque-box">
<p><strong>Pistis Sophia</strong> ("Fé-Sabedoria") é um dos textos mais importantes da tradição gnóstica. Descoberto no século XVIII, este manuscrito coptah do século III d.C. apresenta um diálogo entre Jesus ressurreto e seus discípulos, revelando o drama cósmico da Alma em sua queda e redenção — uma metáfora poderosa para a jornada espiritual de cada ser humano.</p>
</div>

<h2>O Que é Pistis Sophia?</h2>
<p>O nome "Pistis Sophia" combina duas palavras gregas: <em>Pistis</em> (fé, confiança) e <em>Sophia</em> (sabedoria). No texto, Sophia é uma entidade divina que habita as regiões superiores do Pleroma (a Plenitude divina), mas que, movida por um desejo de conhecer o Inefável, inicia uma jornada que a leva à queda nos reinos inferiores. Este mito não é apenas uma história cosmológica — é o retrato da alma humana em sua busca por sentido, seu esquecimento de si mesma e seu eventual retorno à fonte.</p>
<p>O texto, escrito em formato de perguntas e respostas, mostra Jesus instruindo Maria Madalena, João, Pedro e outros discípulos sobre os mistérios dos céus, o poder dos arcontes (forças que aprisionam a alma) e o caminho de ascensão.</p>

<blockquote>Jesus disse a seus discípulos: "Buscai e encontrai; quando encontrardes, maravilhai-vos; quando vos maravilhardes, reinareis; e quando reinardes, descansareis."</blockquote>

<h2>A Cosmologia de Pistis Sophia</h2>
<p>Compreender Pistis Sophia requer familiaridade com três conceitos fundamentais da cosmologia gnóstica:</p>

<h3>Pleroma — A Plenitude</h3>
<p>O <strong>Pleroma</strong> é a totalidade das emanações divinas (<em>aeons</em>), que em sua unidade constituem o Deus transcendente. É o mundo da luz, da perfeição, da unidade primordial. No Pleroma, todas as coisas existem em harmonia, em uma dança de emanações e retornos. Sophia é um dos aeons mais jovens e poderosos deste reino.</p>

<h3>Kenoma — O Vazio</h3>
<p>O <strong>Kenoma</strong> é o mundo material, o reino da falta e da limitação. Criado não pelo Deus verdadeiro, mas por um demiurgo (uma força ignorante que se julga o único deus), o Kenoma é o mundo onde vivemos — um lugar de sofrimento, esquecimento e ilusão. As almas humanas são centelhas divinas aprisionadas neste mundo, tendo esquecido sua origem celestial.</p>

<h3>Chaos — O Caos</h3>
<p>Entre o Pleroma e o Kenoma existe uma região intermediária chamada <strong>Chaos</strong>. É para cá que Sophia cai quando, em sua busca ousada pelo Inefável, ela se separa de sua contraparte (sizígia) e é aprisionada pelas forças dos arcontes. No Chaos, Sophia experimenta sofrimento, confusão e escuridão — uma metáfora da alma que perdeu seu caminho.</p>

<h2>A Queda e Redenção de Sophia</h2>
<p>O drama de Sophia é o drama de toda alma humana. Ela deseja conhecer a fonte última — o Pai Inefável — mas, ao estender-se além de seus limites, cai. No Chaos, ela canta hinos de arrependimento (os "Cânticos da Luz"), clama por ajuda e gradualmente desperta para sua verdadeira natureza. Jesus, após sua ressurreição, desce para resgatá-la, mostrando-lhe o caminho de volta através dos reinos celestiais.</p>
<p>Este mito é profundamente psicológico. Sophia representa a <strong>alma humana em busca</strong> — que cai no materialismo, na identificação com o ego e no sofrimento, mas que pode despertar e retornar à sua fonte através do conhecimento (<em>gnosis</em>) e da transformação interior.</p>

<ul>
<li><strong>Queda</strong> — O movimento de separação, o nascimento do ego</li>
<li><strong>Cativeiro</strong> — A identificação com o mundo material, o sofrimento</li>
<li><strong>Despertar</strong> — O chamado interior, o início do trabalho sobre si</li>
<li><strong>Purificação</strong> — A dissolução dos apegos e identificações</li>
<li><strong>Ascensão</strong> — O retorno à unidade, a realização do Ser</li>
</ul>

<div class="destaque-box">
<p><strong>Lição para o buscador:</strong> O mito de Pistis Sophia nos ensina que a queda não é um pecado, mas uma oportunidade. É no sofrimento do mundo material que a alma desperta para sua verdadeira natureza e inicia a jornada de volta à casa do Pai. Como diz o oráculo de Delfos: "Conhece-te a ti mesmo". Este conhecimento é a chave da redenção.</p>
</div>

<h2>Pistis Sophia e o Trabalho Interior</h2>
<p>Para o praticante do trabalho interior, Pistis Sophia oferece um mapa psicológico e espiritual de rara profundidade. Cada arconte que Sophia encontra em sua ascensão corresponde a um aspecto psicológico que precisa ser compreendido e transcendido. Cada cântico que ela entoa é uma prática de auto-observação e entrega.</p>
<p>O texto também enfatiza a importância da <strong>comunidade espiritual</strong> — o grupo de buscadores que se apoiam mutuamente no caminho. Jesus não resgata Sophia sozinha; ele envia seus discípulos como auxiliares no trabalho de redenção. Este ensinamento ecoa a importância das comunidades de prática espiritual, onde o trabalho interior é compartilhado e aprofundado.</p>
HTML;
        $tags2 = 'pistis sophia,gnosticismo,cosmologia gnóstica,sophia,pleroma,kenoma,alma,redenção';
        $fonte2 = 'Pistis Sophia (Manuscrito copta Askew); Hans Jonas, "A Religião Gnóstica"';
        inserirArtigo($pdo, $cid, $titulo2, $slug2, $resumo2, $conteudo2, $tags2, $fonte2, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug2;
    }

    $slug3 = 'os-evangelhos-apocrifos-sabedoria-alem-do-canon';
    if (!existeSlug($pdo, $slug3)) {
        $titulo3 = 'Os Evangelhos Apócrifos: Sabedoria Além do Cânon';
        $resumo3 = 'Os evangelhos excluídos da Bíblia oficial preservam ensinamentos profundos sobre o autoconhecimento, o divino feminino e a natureza da realidade. Uma jornada pelos textos perdidos do cristianismo primitivo.';
        $conteudo3 = <<<'HTML'
<div class="destaque-box">
<p>O Novo Testamento que conhecemos hoje é o resultado de um processo de seleção — e exclusão — que durou séculos. Os evangelhos apócrifos, longe de serem "falsificações", representam tradições cristãs tão antigas quanto as canônicas, mas que foram marginalizadas por razões teológicas e políticas. Neles encontra-se uma <strong>sabedoria profunda</strong> sobre a natureza de Cristo, o autoconhecimento e o caminho espiritual.</p>
</div>

<h2>Por Que Foram Excluídos?</h2>
<p>O processo de formação do cânon bíblico foi gradual e complexo. Vários fatores contribuíram para a exclusão dos evangelhos apócrifos:</p>
<ol>
<li><strong>Cristologia divergente</strong> — Enquanto os evangelhos canônicos (Mateus, Marcos, Lucas e João) apresentam Jesus como Deus encarnado que morre pelos pecados da humanidade, os apócrifos frequentemente enfatizam Jesus como mestre de sabedoria que revela conhecimentos esotéricos aos discípulos.</li>
<li><strong>Ênfase no conhecimento interior</strong> — Textos como o Evangelho de Tomé e o Evangelho da Verdade enfatizam a experiência direta do Divino, em oposição à autoridade eclesiástica e aos sacramentos institucionais.</li>
<li><strong>Papel do divino feminino</strong> — Maria Madalena aparece como discípula predileta em vários textos apócrifos, recebendo revelações especiais de Jesus. Em uma sociedade patriarcal, essa ênfase era desconfortável para a hierarquia da Igreja.</li>
<li><strong>Interpretação simbólica da ressurreição</strong> — Muitos textos gnósticos interpretam a ressurreição não como um evento histórico, mas como um despertar interior, o que entrava em conflito com a doutrina oficial.</li>
</ol>

<h2>O Evangelho de Tomé</h2>
<p>Descoberto em Nag Hammadi em 1945, o <strong>Evangelho de Tomé</strong> é uma coleção de 114 ditos atribuídos a Jesus. Diferentemente dos evangelhos canônicos, não há narrativa de nascimento, milagres, morte ou ressurreição. Apenas as palavras do mestre — palavras que convidam ao autoconhecimento radical.</p>

<blockquote>Jesus disse: "Conhecei o que está diante de vossos olhos, e o que está oculto vos será revelado. Pois nada há de oculto que não se manifeste." — Evangelho de Tomé, dito 5</blockquote>

<p>Tomé nos convida a ir além das crenças e dogmas, buscando a experiência direta do Reino. O "Reino" não é um lugar após a morte, mas um estado de consciência acessível aqui e agora — "espalhado pela terra, e os homens não o veem" (dito 113).</p>

<h2>O Evangelho de Filipe</h2>
<p>O <strong>Evangelho de Filipe</strong> é um texto profundamente simbólico que aborda os sacramentos gnósticos, a relação entre o masculino e o feminino no Divino, e a importância do matrimônio espiritual (sizígia). Ele menciona que Maria Madalena era a "companheira" de Jesus, e que ele a beijava frequentemente na boca — não por razões carnais, mas como símbolo da união entre a alma e o Espírito.</p>
<p>Filipe nos ensina que a verdadeira salvação não é individual, mas relacional. Não podemos despertar sozinhos — precisamos uns dos outros para refletir nossa verdadeira natureza.</p>

<h2>O Evangelho da Verdade</h2>
<p>Atribuído a Valentim, o grande mestre gnóstico do século II, o <strong>Evangelho da Verdade</strong> é um sermão poético de extraordinária beleza. Ele descreve o estado de ignorância como um "pesadelo" do qual despertamos através do conhecimento. Quando a alma reconhece sua verdadeira origem, o medo e a ansiedade se dissolvem, e ela retorna à plenitude do Pai.</p>

<blockquote>"É dentro de cada um que a luz habita, e todos os que creem nela são iluminados. Mas aqueles que não creem, ainda que ouçam o chamado, não se voltam para ela." — Evangelho da Verdade</blockquote>

<h2>O Evangelho de Maria Madalena</h2>
<p>Descoberto em Akhmim, no Egito, o <strong>Evangelho de Maria</strong> (Madalena) apresenta Maria como a discípula que mais profundamente compreendeu os ensinamentos de Jesus. Após a partida do mestre, ela conforta e instrui os outros discípulos, revelando visões e ensinamentos secretos que recebeu. Pedro, em uma passagem emblemática, pergunta: "Jesus falou com uma mulher em segredo, sem nosso conhecimento? Devemos ouvi-la?"</p>
<p>Este texto é um testemunho do papel central das mulheres no cristianismo primitivo e da resistência patriarcal que gradualmente as silenciou.</p>

<div class="destaque-box">
<p><strong>A sabedoria além do cânon:</strong> Os evangelhos apócrifos nos lembram que a verdade espiritual não pode ser contida em um único livro ou doutrina. Cada texto é uma janela para uma experiência diferente do Divino, um convite a buscar o conhecimento onde quer que ele se encontre — mesmo que isso signifique ir além das fronteiras estabelecidas pela tradição oficial.</p>
</div>

<h2>Como Estudar os Apócrifos Hoje</h2>
<p>Estes textos estão disponíveis em traduções acessíveis ao grande público. Para começar, recomenda-se:</p>
<ul>
<li>A Biblioteca de Nag Hammadi completa (tradução de 1977, disponível online)</li>
<li>Estudos comparativos com os evangelhos canônicos</li>
<li>Abordagem experiencial: meditar sobre os ditos de Tomé, praticar a auto-observação como ensinada por Filipe</li>
<li>Participação em grupos de estudo que pratiquem o trabalho interior</li>
</ul>
HTML;
        $tags3 = 'evangelhos apócrifos,evangelho de tomé,evangelho de filipe,evangelho da verdade,maria madalena,nag hammadi,cristianismo primitivo,gnose';
        $fonte3 = 'Nag Hammadi Library; Elaine Pagels, "Os Evangelhos Gnósticos"; Bart Ehrman, "Evangelhos Perdidos"';
        inserirArtigo($pdo, $cid, $titulo3, $slug3, $resumo3, $conteudo3, $tags3, $fonte3, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug3;
    }
}

if (isset($catMap['gnose-esoterismo'])) {
    $cid = $catMap['gnose-esoterismo'];

    $slug = 'o-caminho-da-gnose-conhecimento-que-transforma';
    if (!existeSlug($pdo, $slug)) {
        $titulo = 'O Caminho da Gnose: Conhecimento que Transforma';
        $resumo = 'Gnosis é o conhecimento direto do Divino — não uma crença intelectual, mas uma experiência viva que transforma o ser. Descubra os fundamentos, as ferramentas práticas e o caminho do despertar.';
        $conteudo = <<<'HTML'
<div class="destaque-box">
<p><strong>Gnose</strong> (do grego γνῶσις — conhecimento) é o termo utilizado para designar o conhecimento direto, experiencial e transformador da realidade divina. Diferente da mera crença ou da fé dogmática, a gnose é um saber que se experimenta, que se vive — um conhecimento que não apenas informa, mas <em>transforma</em> quem o possui.</p>
</div>

<h2>Gnose: Mais que Conhecimento Intelectual</h2>
<p>No mundo contemporâneo, tendemos a confundir conhecimento com acúmulo de informações. A gnose, porém, é de uma ordem completamente diferente. Enquanto o conhecimento intelectual opera no domínio da mente discursiva (o <em>nous</em> inferior), a gnose é uma apreensão direta da verdade, que ocorre no nível do <strong>Pneuma</strong> — o espírito humano, a centelha divina que habita em cada um de nós.</p>
<p>As tradições gnósticas distinguem três componentes fundamentais no ser humano:</p>
<ul>
<li><strong>Hyle</strong> (corpo material) — o veículo físico, sujeito ao nascimento e à morte</li>
<li><strong>Psyche</strong> (alma) — a sede das emoções, desejos e da mente discursiva</li>
<li><strong>Pneuma</strong> (espírito) — a centelha divina, nosso verdadeiro ser, que está aprisionada pelo esquecimento</li>
</ul>
<p>O despertar da gnose é precisamente o processo pelo qual o <strong>Pneuma</strong> se reconhece a si mesmo, rompendo o véu do esquecimento e lembrando-se de sua origem divina. É a realização das palavras do oráculo de Delfos: "Conhece-te a ti mesmo, e conhecerás o universo e os deuses".</p>

<h2>O Despertar e o Retorno</h2>
<p>O caminho gnóstico pode ser descrito em duas grandes etapas: despertar e retorno.</p>
<p><strong>Despertar</strong> é o momento em que a alma (Psyche) começa a suspeitar que a realidade material não é tudo o que existe. Inquietações, sonhos vívidos, sincronicidades, um chamado interior — são as primeiras manifestações do Pneuma que começa a se agitar em seu sono profundo. O despertar pode ser gradual ou súbito, provocado por uma crise existencial, um encontro significativo ou o estudo de ensinamentos espirituais.</p>
<p><strong>Retorno</strong> é o processo ativo de transformação que se segue ao despertar. A alma que despertou deve agora trabalhar conscientemente para se libertar das amarras que a prendem ao mundo material. Isso inclui a dissolução do ego falso, a purificação das emoções negativas e o desenvolvimento de virtudes como a compaixão, a franqueza e a fé ativa.</p>

<blockquote>"O conhecimento (gnose) não é um corpo de doutrina, mas um caminho de transformação. Quem busca apenas compreender, sem se transformar, ainda não encontrou a gnose." — Adaptação de ensinamentos gnósticos</blockquote>

<h2>Ferramentas Práticas para o Caminho</h2>
<p>O gnosticismo não é uma filosofia especulativa, mas uma <strong>via prática</strong>. Aqui estão algumas ferramentas fundamentais para quem deseja trilhar este caminho:</p>

<h3>Auto-observação</h3>
<p>A auto-observação é a base do trabalho interior. Consiste em observar a si mesmo sem julgamento — pensamentos, emoções, sensações corporais, reações automáticas. O objetivo não é mudar nada, simplesmente <strong>testemunhar</strong>. Com o tempo, a auto-observação revela os padrões mecânicos da personalidade, as identificações do ego e as defesas psicológicas que nos mantêm adormecidos.</p>
<p>Prática diária: 10-15 minutos de auto-observação, preferencialmente pela manhã e à noite. Observe-se em situações cotidianas — ao conversar, ao trabalhar, ao comer. Note como a mente reage, como o corpo sente, como as emoções fluem.</p>

<h3>Meditação</h3>
<p>A meditação gnóstica não é passiva. Ela envolve a <strong>atenção consciente</strong> e a <strong>visualização criativa</strong>. Técnicas básicas incluem:</p>
<ul>
<li><strong>Meditação respiratória</strong> — foco na respiração (4 tempos: inspira/4 seg, retém/4, expira/4, retém/4)</li>
<li><strong>Meditação nos chakras</strong> — visualização e ativação dos centros energéticos do corpo sutil</li>
<li><strong>Meditação no coração</strong> — cultivar o estado de amor-comp放松ão universal, sentindo o coração como centro do ser</li>
</ul>

<h3>Dissolução do Ego</h3>
<p>O ego — o "eu" ilusório que construímos a partir de nossas experiências, crenças e condicionamentos — é o principal obstáculo à gnose. A dissolução do ego não significa aniquilação da personalidade, mas a <strong>transcendência da identificação</strong> com ela. Quando deixamos de nos identificar com nossos pensamentos, emoções e papéis sociais, descobrimos quem realmente somos — o Pneuma, o observador silencioso que sempre esteve lá.</p>

<div class="destaque-box">
<p><strong>Lembre-se:</strong> O caminho da gnose não é fácil. Exige disciplina, sinceridade e coragem para enfrentar as próprias sombras. Mas é também o caminho da verdadeira liberdade — a liberdade do ser que se conhece a si mesmo e, conhecendo-se, conhece o Divino.</p>
</div>
HTML;
        $tags = 'gnose,gnosticismo,conhecimento direto,pneuma,psyche,auto-observação,meditação,eispielo,despertar espiritual';
        $fonte = 'Tradição Gnóstica; Samael Aun Weor, "A Revolução da Dialética"; Hans Jonas, "A Religião Gnóstica"';
        inserirArtigo($pdo, $cid, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug;
    }
}

if (isset($catMap['consciencia-meditacao'])) {
    $cid = $catMap['consciencia-meditacao'];

    $slug = 'expansao-da-consciencia-alem-dos-limites-da-mente-comum';
    if (!existeSlug($pdo, $slug)) {
        $titulo = 'Expansão da Consciência: Além dos Limites da Mente Comum';
        $resumo = 'A consciência humana pode transcender seus limites habituais. Explore os estados expandidos de consciência, os obstáculos internos e as técnicas meditativas que abrem as portas da percepção.';
        $conteudo = <<<'HTML'
<div class="destaque-box">
<p>A consciência humana não é fixa. Embora nosso estado habitual — o chamado "estado de vigília" — pareça ser o único possível, as tradições de sabedoria de todo o mundo nos ensinam que é possível <strong>expandir a consciência</strong> para além de seus limites comuns. Esta expansão não é um fenômeno místico reservado a iluminados, mas uma possibilidade acessível a todo aquele que se dedica ao trabalho interior.</p>
</div>

<h2>O Que São Estados de Consciência?</h2>
<p>Nosso cérebro opera em diferentes frequências — os chamados <strong>estados de ondas cerebrais</strong>. Cada frequência corresponde a um estado de consciência distinto:</p>

<ul>
<li><strong>Beta (14-30 Hz)</strong> — Estado de vigília ativa, alerta, foco externo. Predominante no dia a dia.</li>
<li><strong>Alpha (8-14 Hz)</strong> — Relaxamento, sonho acordado, criatividade. A ponte entre o consciente e o subconsciente.</li>
<li><strong>Teta (4-8 Hz)</strong> — Meditação profunda, sonhos, acesso ao subconsciente. Estados criativos e intuitivos.</li>
<li><strong>Delta (0,5-4 Hz)</strong> — Sono profundo sem sonhos, regeneração, conexão com o inconsciente coletivo.</li>
<li><strong>Gama (30-100 Hz)</strong> — Estados de alta integração cognitiva, insight, percepção unificada.</li>
</ul>

<p>O estado de "consciência comum" é dominado por ondas Beta. As práticas meditativas visam, em primeiro lugar, desacelerar o cérebro para estados Alpha e Teta — onde a percepção se torna mais sutil, a intuição mais aguçada e o senso de identidade começa a se expandir.</p>

<h2>Os Três Grandes Obstáculos</h2>
<p>Por que a expansão da consciência não ocorre naturalmente? Três obstáculos principais nos mantêm prisioneiros do estado comum:</p>

<ol>
<li><strong>Identificação</strong> — Estamos tão identificados com nossos pensamentos, emoções e sensações que nos confundimos com eles. Não percebemos que somos o <em>observador</em> dos pensamentos, não os pensamentos em si. Esta identificação constante nos mantém presos à superfície da mente.</li>
<li><strong>Condicionamento</strong> — Nossa mente foi programada por anos de educação, cultura e experiências pessoais. Reagimos automaticamente a estímulos, repetindo padrões que nem sequer questionamos. O condicionamento cria uma "realidade virtual" que tomamos como verdade absoluta.</li>
<li><strong>Ego</strong> — O senso de "eu" que construímos é uma entidade frágil que precisa constantemente se afirmar, defender e expandir. O ego teme a dissolução que acompanha a expansão da consciência, e por isso resiste ferozmente ao trabalho interior.</li>
</ol>

<blockquote>"O maior obstáculo à expansão da consciência é a certeza de que já estamos conscientes. Enquanto acreditarmos que nosso estado habitual é o único possível, não buscaremos nada além."</blockquote>

<h2>Técnicas de Meditação para Expansão</h2>

<h3>Respiração 4x4</h3>
<p>Uma técnica simples e poderosa para acalmar a mente e alterar o estado de consciência:</p>
<ol>
<li>Sente-se confortavelmente com a coluna ereta</li>
<li>Inspire profundamente pelo nariz contando até 4</li>
<li>Retenha o ar contando até 4</li>
<li>Expire lentamente pela boca contando até 4</li>
<li>Mantenha os pulmões vazios contando até 4</li>
<li>Repita por 5 a 10 minutos</li>
</ol>
<p>Esta respiração ritmada (conhecida como "respiração quadrada" ou <em>sama vritti</em>) equilibra o sistema nervoso autônomo e induz estados de relaxamento profundo, facilitando a transição para ondas Alpha e Teta.</p>

<h3>Meditação nos Chakras</h3>
<p>Os chakras são centros energéticos do corpo sutil descritos pelas tradições tântricas e yogues. Cada chakra corresponde a um estado de consciência específico. A meditação nos chakras envolve visualizar luz e cor em cada centro, ativando e equilibrando diferentes aspectos do ser.</p>
<ul>
<li><strong>Muladhara (base da coluna)</strong> — Segurança, sobrevivência, enraizamento</li>
<li><strong>Svadhisthana (abdômen inferior)</strong> — Prazer, emoções, criatividade</li>
<li><strong>Manipura (plexo solar)</strong> — Poder pessoal, vontade, transformação</li>
<li><strong>Anahata (coração)</strong> — Amor, compaixão, equilíbrio</li>
<li><strong>Vishuddha (garganta)</strong> — Comunicação, verdade, expressão</li>
<li><strong>Ajna (entre as sobrancelhas)</strong> — Intuição, percepção sutil, terceiro olho</li>
<li><strong>Sahasrara (topo da cabeça)</strong> — Conexão divina, transcendência, unidade</li>
</ul>

<h3>Auto-observação</h3>
<p>A auto-observação é a ferramenta fundamental para romper a identificação. Ao observarmos nossos pensamentos e emoções como se fossem fenômenos naturais — sem julgá-los ou nos identificar com eles — começamos a criar um espaço interior, uma "testemunha silenciosa" que é o primeiro passo para a expansão da consciência.</p>

<div class="destaque-box">
<p><strong>Expansão não é fuga:</strong> É importante compreender que expandir a consciência não significa escapar da realidade, mas sim perceber mais profundamente a realidade que já está presente. A consciência expandida vê o mesmo mundo com outros olhos — olhos que percebem a interconexão, a beleza e o sagrado em todas as coisas.</p>
</div>

<h2>A Prática Diária</h2>
<p>Para quem deseja iniciar o caminho da expansão consciente, recomenda-se:</p>
<ul>
<li>5-10 minutos de respiração consciente ao acordar</li>
<li>Auto-observação em momentos-chave do dia (ao comer, ao caminhar, ao conversar)</li>
<li>15-20 minutos de meditação à noite, preferencialmente com foco na respiração ou nos chakras</li>
<li>Registro em diário das percepções e insights</li>
</ul>
HTML;
        $tags = 'consciência,meditação,expansão da consciência,ondas cerebrais,auto-observação,chakras,respiração,alpha,teta,delta,gama';
        $fonte = 'Tradições yogues e tântricas; Samael Aun Weor, "Tratado de Medicina Oculta"; Estudos de neurociência da meditação';
        inserirArtigo($pdo, $cid, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug;
    }
}

if (isset($catMap['trabalho-interior'])) {
    $cid = $catMap['trabalho-interior'];

    $slug = 'personalidade-essencia-e-ego-a-psicologia-do-ser';
    if (!existeSlug($pdo, $slug)) {
        $titulo = 'Personalidade, Essência e Ego: A Psicologia do Ser';
        $resumo = 'O ser humano é composto por três aspectos fundamentais: Essência (quem realmente somos), Personalidade (quem aprendemos a ser) e Ego (quem pensamos ser). Compreender esta tríade é a chave do trabalho interior.';
        $conteudo = <<<'HTML'
<div class="destaque-box">
<p>O trabalho interior se fundamenta em uma compreensão precisa da psicologia humana. Diferente da psicologia acadêmica, que estuda a mente a partir de fora, a <strong>psicologia do ser</strong> aborda o ser humano de dentro — como uma unidade de corpo, alma e espírito que pode (e deve) ser transformada conscientemente. Neste artigo, exploramos os três componentes fundamentais do ser segundo a tradição gnóstica.</p>
</div>

<h2>Os Três Componentes</h2>

<h3>Essência</h3>
<p>A <strong>Essência</strong> é quem realmente somos — nossa natureza divina, o Pneuma ou centelha espiritual que existe em cada ser humano. Ela é eterna, imutável, perfeita. Mas na maioria das pessoas, a Essência está adormecida, soterrada sob camadas de condicionamento e identificações.</p>
<p>A Essência não precisa ser criada ou desenvolvida — ela apenas precisa ser <em>lembrada</em> e <em>liberada</em>. O trabalho interior é essencialmente o processo de libertar a Essência do cativeiro do ego e da personalidade mecânica.</p>

<blockquote>"O Reino de Deus está dentro de vós. Mas a Essência não pode se manifestar enquanto o ego ocupar o trono."</blockquote>

<h3>Personalidade</h3>
<p>A <strong>Personalidade</strong> é o conjunto de características psicológicas, comportamentos, crenças e valores que adquirimos ao longo da vida. Ela é formada por três grandes influências:</p>
<ol>
<li><strong>Herança genética e temperamental</strong> — nossa base biológica</li>
<li><strong>Educação e cultura</strong> — valores, crenças e comportamentos ensinados</li>
<li><strong>Experiências pessoais</strong> — traumas, alegrias, aprendizagens que moldaram quem somos</li>
</ol>
<p>A personalidade não é ruim em si mesma. Ela é necessária para funcionarmos no mundo. O problema é quando nos <strong>identificamos</strong> completamente com ela, esquecendo que existe algo mais profundo — a Essência.</p>

<h3>Ego (o "Eu" Falso)</h3>
<p>O <strong>Ego</strong> ou "eu" falso é a parte mais problemática do ser humano. Ele surge quando a personalidade se identifica com seus conteúdos e se considera separada do todo. O ego é o centro de gravidade psicológica que organiza a vida em torno de si mesmo — seus desejos, seus medos, suas ambições, suas defesas.</p>
<p>O ego não é uma entidade fixa, mas um <strong>processo</strong> de identificação constante. Características do ego incluem:</p>
<ul>
<li>Orgulho e vaidade</li>
<li>Medo de perder o controle</li>
<li>Necessidade de estar certo</li>
<li>Comparação com os outros</li>
<li>Apego a opiniões e crenças</li>
<li>Autocomiseração e autojulgamento</li>
</ul>

<h2>Os Três Fatores da Revolução da Consciência</h2>
<p>A tradição gnóstica ensina que existem três fatores fundamentais para a transformação interior. São eles:</p>

<h3>1. Nascer — Conhecer a Si Mesmo</h3>
<p>O primeiro fator é o <strong>autoconhecimento</strong>. É preciso conhecer a própria Essência, reconhecer o ego e compreender a mecânica da personalidade. Este conhecimento não é intelectual — é um conhecimento experiencial que surge da auto-observação sincera e constante.</p>
<p>Nascer significa despertar a Essência adormecida, permitir que ela comece a se manifestar em nossa vida. É um segundo nascimento, como Jesus disse a Nicodemos: "Em verdade, em verdade te digo: quem não nascer de novo não pode ver o Reino de Deus."</p>

<h3>2. Morrer — Dissolver o Ego</h3>
<p>O segundo fator é a <strong>morte psicológica</strong> — a dissolução progressiva do ego e de todos os seus agregados psicológicos. Morrer significa deixar ir: o orgulho, o medo, a vaidade, o ressentimento, a inveja. Cada defeito psicológico que é compreendido e transcendido é uma pequena morte do ego.</p>
<p>Este fator é o mais difícil, pois o ego luta pela própria sobrevivência. Ele se disfarça, cria justificativas, projeta culpas nos outros. A morte psicológica exige sinceridade radical e a disposição de enfrentar as próprias sombras.</p>

<h3>3. Sacrificar — Servir ao Próximo</h3>
<p>O terceiro fator é o <strong>sacrifício consciente pelo bem dos outros</strong>. Sacrificar não significa sofrer, mas <strong>dar de si mesmo</strong> — tempo, energia, conhecimento — em benefício do próximo. O sacrifício dissolve o egoísmo e conecta o ser humano ao todo.</p>
<p>Este fator completa os outros dois. De nada adianta conhecer a si mesmo e dissolver o ego se o resultado for um isolamento egoísta. O verdadeiro despertar se manifesta como compaixão ativa e serviço consciente.</p>

<div class="destaque-box">
<p><strong>A inter-relação dos três fatores:</strong> Os três fatores não são etapas lineares, mas aspectos simultâneos de um único processo. Enquanto nos conhecemos, vamos morrendo para o ego. Enquanto morremos, vamos descobrindo a Essência. E enquanto servimos, os dois primeiros fatores se aprofundam e se consolidam.</p>
</div>

<h2>Estágios do Trabalho Interior</h2>
<p>O trabalho interior pode ser compreendido em estágios progressivos:</p>
<ol>
<li><strong>Despertar</strong> — Tomar consciência de que existe um trabalho a ser feito</li>
<li><strong>Auto-observação</strong> — Aprender a observar a si mesmo sem julgamento</li>
<li><strong>Conhecimento de si</strong> — Identificar os principais agregados psicológicos (defeitos)</li>
<li><strong>Trabalho com os defeitos</strong> — Compreender cada defeito, sua origem, sua mecânica</li>
<li><strong>Morte psicológica</strong> — Dissolver progressivamente cada agregado</li>
<li><strong>Manifestação da Essência</strong> — Permitir que a Essência se expresse na vida cotidiana</li>
</ol>

<h2>A Prática da Auto-observação</h2>
<p>A auto-observação é a ferramenta central do trabalho interior. Eis como praticá-la:</p>
<ul>
<li><strong>Momento fixo</strong> — Reserve 10-15 minutos ao dia para se sentar em silêncio e observar seus processos internos</li>
<li><strong>Sem julgamento</strong> — Observe pensamentos e emoções como fenômenos naturais, sem rotulá-los como bons ou maus</li>
<li><strong>No cotidiano</strong> — Pratique a auto-observação em situações comuns: ao comer, ao andar, ao trabalhar</li>
<li><strong>Registro</strong> — Anote suas observações em um diário, identificando padrões e insights</li>
</ul>
HTML;
        $tags = 'psicologia gnóstica,essência,personalidade,ego,revolução da consciência,auto-observação,morte psicológica,trabalho interior';
        $fonte = 'Samael Aun Weor, "A Revolução da Dialética" e "Tratado de Psicologia Revolucionária"; Gurdjieff, "Fragmentos de um Ensinamento Desconhecido"';
        inserirArtigo($pdo, $cid, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug;
    }
}

if (isset($catMap['corpo-regeneracao'])) {
    $cid = $catMap['corpo-regeneracao'];

    $slug = 'autofagia-epigenetica-e-regeneracao';
    if (!existeSlug($pdo, $slug)) {
        $titulo = 'Autofagia, Epigenética e Regeneração';
        $resumo = 'A ciência moderna começa a validar o que as tradições ancestrais sempre souberam: o corpo possui mecanismos inatos de regeneração. Descubra como autofagia, epigenética e coerência cardíaca podem transformar sua saúde.';
        $conteudo = <<<'HTML'
<div class="destaque-box">
<p>A sabedoria ancestral sempre ensinou que o corpo humano não é uma máquina estática, mas um organismo vivo dotado de incríveis capacidades de autorregeneração. Hoje, a ciência contemporânea — através da biologia molecular, da epigenética e da neurocardiologia — está confirmando estas verdades milenares. Este artigo explora a interseção entre a ciência moderna e as práticas ancestrais de regeneração.</p>
</div>

<h2>Autofagia: O Sistema de Limpeza Celular</h2>
<p>A <strong>autofagia</strong> (do grego "auto" = si mesmo, "fagia" = comer) é o processo pelo qual as células reciclam e eliminam componentes danificados, disfuncionais ou desnecessários. Descoberta pelo cientista japonês Yoshinori Ohsumi, que recebeu o Prêmio Nobel de Medicina em 2016 por seus estudos, a autofagia é essencial para a manutenção da saúde celular.</p>
<p>Durante a autofagia, a célula "come" partes de si mesma — proteínas malformadas, organelas envelhecidas, resíduos metabólicos — quebrando-as e reciclando seus componentes. Este processo é fundamental para:</p>
<ul>
<li><strong>Limpeza celular</strong> — eliminação de toxinas e resíduos acumulados</li>
<li><strong>Prevenção do envelhecimento</strong> — remoção de componentes danificados que aceleram o declínio celular</li>
<li><strong>Proteção contra doenças</strong> — especialmente neurodegenerativas e câncer</li>
<li><strong>Regeneração tecidual</strong> — estímulo à renovação celular</li>
</ul>

<h3>Como Ativar a Autofagia</h3>
<p>As formas mais eficazes de estimular a autofagia incluem:</p>
<ol>
<li><strong>Jejum intermitente</strong> — períodos de 16-24 horas sem alimentação ativam fortemente a autofagia</li>
<li><strong>Exercício físico</strong> — especialmente em jejum, potencializa o processo</li>
<li><strong>Restrição calórica</strong> — redução moderada da ingestão calórica diária</li>
<li><strong>Dieta low-carb/ectogênica</strong> — baixa ingestão de carboidratos promove autofagia</li>
</ol>

<blockquote>"O jejum é uma prática comum a todas as grandes tradições espirituais. Hoje sabemos que ele não apenas purifica a alma, mas também ativa mecanismos profundos de regeneração do corpo."</blockquote>

<h2>Epigenética: O Ambiente que Molda os Genes</h2>
<p>A <strong>epigenética</strong> (do grego "epi" = sobre, além de) é o estudo das modificações na expressão gênica que não alteram a sequência do DNA, mas podem ser herdadas. Em termos simples: seus genes não são seu destino. O ambiente, o estilo de vida e até seus pensamentos e emoções podem influenciar quais genes são ativados (expressos) e quais permanecem silenciados.</p>
<p>Fatores que influenciam a expressão epigenética:</p>
<ul>
<li><strong>Alimentação</strong> — nutrientes, vitaminas, compostos bioativos dos alimentos</li>
<li><strong>Estresse</strong> — hormônios do estresse (cortisol) alteram a metilação do DNA</li>
<li><strong>Sono</strong> — a qualidade do sono afeta a expressão de genes relacionados à reparação celular</li>
<li><strong>Exercício</strong> — ativa genes benéficos e silencia genes inflamatórios</li>
<li><strong>Práticas contemplativas</strong> — meditação e oração alteram padrões epigenéticos relacionados à inflamação e ao estresse</li>
</ul>
<p>Um estudo pioneiro do Dr. Dean Ornish demonstrou que mudanças intensivas no estilo de vida (dieta, exercício, gestão do estresse e apoio social) podem alterar a expressão de mais de 500 genes em apenas três meses, incluindo a ativação de genes protetores e o silenciamento de genes associados ao câncer.</p>

<h2>Coerência Cardíaca</h2>
<p>A ciência do coração, liderada pelo <strong>HeartMath Institute</strong>, revelou que o coração não é apenas uma bomba mecânica. Ele possui seu próprio sistema nervoso intrínseco (o "coração cerebral"), com aproximadamente <strong>40.000 neurônios</strong> — um cérebro em miniatura no peito.</p>
<p>A <strong>coerência cardíaca</strong> é um estado fisiológico no qual o coração, o cérebro e o sistema nervoso entram em sincronia. Neste estado, a variabilidade da frequência cardíaca (VFC) se torna ordenada e rítmica, produzindo benefícios profundos:</p>
<ul>
<li>Redução do estresse e da ansiedade</li>
<li>Melhora da função imunológica</li>
<li>Aumento da clareza mental e da intuição</li>
<li>Equilíbrio hormonal</li>
<li>Sensação de bem-estar e conexão</li>
</ul>

<h3>Práticas Regenerativas Cotidianas</h3>
<p>Para integrar a regeneração na vida diária:</p>
<ol>
<li><strong>Jejum intermitente</strong> — comece com 14-16 horas de jejum, 3-4 vezes por semana</li>
<li><strong>Respiração coerente</strong> — 5 minutos de respiração ritmada (5 segundos inspira, 5 segundos expira)</li>
<li><strong>Contato com a natureza</strong> — mínimo 20 minutos ao ar livre diariamente</li>
<li><strong>Meditação</strong> — 10-15 minutos de prática contemplativa</li>
<li><strong>Movimento consciente</strong> — caminhada, alongamento ou prática energética como Tai Chi</li>
</ol>

<div class="destaque-box">
<p><strong>O corpo como templo:</strong> As tradições ancestrais sempre consideraram o corpo como o templo do espírito. A ciência moderna está apenas começando a compreender a profundidade desta sabedoria. Cuidar do corpo é cuidar do veículo que nos permite despertar, trabalhar e servir.</p>
</div>
HTML;
        $tags = 'autofagia,epigenética,regeneração,jejum intermitente,coerência cardíaca,heartmath,nobel ohsumi,saúde integral';
        $fonte = 'Yoshinori Ohsumi (Prêmio Nobel 2016); Dean Ornish, "O Poder dos Genes"; HeartMath Institute; Valter Longo, "A Dieta da Longevidade"';
        inserirArtigo($pdo, $cid, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug;
    }
}

if (isset($catMap['hermetismo-teosofia'])) {
    $cid = $catMap['hermetismo-teosofia'];

    $slug = 'hermetismo-e-teosofia-as-duas-grandes-correntes';
    if (!existeSlug($pdo, $slug)) {
        $titulo = 'Hermetismo e Teosofia: As Duas Grandes Correntes';
        $resumo = 'O Hermetismo e a Teosofia são duas das mais importantes correntes da sabedoria ocidental. Embora distintas, ambas apontam para as mesmas verdades universais que constituem a Filosofia Perene.';
        $conteudo = <<<'HTML'
<div class="destaque-box">
<p><strong>Hermetismo</strong> e <strong>Teosofia</strong> são duas grandes correntes de sabedoria que moldaram o pensamento esotérico ocidental. Embora distintas em origem e desenvolvimento, ambas compartilham um núcleo comum de verdades espirituais universais — o que Leibniz chamou de <em>Philosophia Perennis</em>. Este artigo explora ambas as tradições, seus ensinamentos fundamentais e seus pontos de convergência.</p>
</div>

<h2>Hermetismo: A Sabedoria de Hermes Trismegisto</h2>
<p>O <strong>Hermetismo</strong> é uma tradição filosófico-espiritual que remonta ao Egito ptolomaico (séculos II-III d.C.), atribuída a <strong>Hermes Trismegisto</strong> — figura lendária que combina o deus egípcio Thoth (deus da sabedoria, escrita e magia) com o deus grego Hermes (mensageiro dos deuses, guia das almas).</p>
<p>Os textos herméticos, reunidos no <em>Corpus Hermeticum</em>, foram preservados e estudados ao longo dos séculos por alquimistas, magos, filósofos e cientistas — desde o Renascimento até os dias atuais. O Hermetismo influenciou profundamente figuras como Marsilio Ficino, Pico della Mirandola, Giordano Bruno, Isaac Newton e Carl Jung.</p>

<h2>Os 7 Princípios Herméticos</h2>
<p>O <em>Caibalion</em> (um texto hermético do século XIX) apresenta os sete princípios fundamentais do Hermetismo. Estes princípios são leis universais que governam toda a realidade — do macrocosmo ao microcosmo:</p>

<ol>
<li><strong>Princípio do Mentalismo</strong> — "O Todo é Mente; o universo é mental." Toda a criação tem origem em uma Consciência Una, da qual tudo emana.</li>
<li><strong>Princípio da Correspondência</strong> — "O que está em cima é como o que está embaixo; o que está dentro é como o que está fora." Os mesmos padrões se repetem em todos os níveis da realidade.</li>
<li><strong>Princípio da Vibração</strong> — "Nada está parado; tudo se move; tudo vibra." Toda matéria e toda energia são manifestações de diferentes frequências vibratórias.</li>
<li><strong>Princípio da Polaridade</strong> — "Tudo é duplo; tudo tem dois polos; os opostos são idênticos em natureza, diferindo apenas em grau." O bem e o mal, o amor e o ódio, o positivo e o negativo — são polos do mesmo continuum.</li>
<li><strong>Princípio do Ritmo</strong> — "Tudo tem fluxo e refluxo; tudo sobe e desce; o ritmo é a compensação." A vida é cíclica — estações, fases da lua, marés, ciclos biológicos.</li>
<li><strong>Princípio de Causa e Efeito</strong> — "Toda causa tem seu efeito; todo efeito tem sua causa." Nada acontece por acaso; o universo opera segundo leis precisas.</li>
<li><strong>Princípio de Geração</strong> — "A geração existe em tudo; tudo tem seu princípio masculino e feminino." A criatividade universal se manifesta através da interação entre forças complementares.</li>
</ol>

<blockquote>"Quem compreende estes princípios compreende a chave de toda a realidade. Quem os aplica em sua vida torna-se senhor de si mesmo e do universo."</blockquote>

<h2>Teosofia: A Sabedoria Divina</h2>
<p>A <strong>Teosofia</strong> (do grego "theos" = deus, "sophia" = sabedoria) é uma tradição espiritual moderna fundada por <strong>Helena Petrovna Blavatsky</strong> (1831-1891) e outros no final do século XIX. A Sociedade Teosófica, fundada em 1875 em Nova York, propunha três objetivos fundamentais:</p>
<ul>
<li>Formar um núcleo de fraternidade universal da humanidade, sem distinção de raça, credo, sexo, casta ou cor</li>
<li>Promover o estudo comparativo das religiões, filosofias e ciências</li>
<li>Investigar as leis inexplicadas da natureza e os poderes latentes do ser humano</li>
</ul>
<p>A obra magna de Blavatsky, <em>A Doutrina Secreta</em> (1888), é um monumento de erudição esotérica que tenta sintetizar a sabedoria de todas as grandes tradições — desde o hinduísmo e budismo até o hermetismo e o gnosticismo — em uma cosmovisão unificada.</p>

<h2>Pontos de Convergência</h2>
<p>Hermetismo e Teosofia, embora distintos, convergem em vários pontos fundamentais:</p>

<ol>
<li><strong>Unidade fundamental</strong> — Ambas afirmam que existe uma Realidade Una, da qual tudo emana e à qual tudo retorna</li>
<li><strong>Correspondência macro-microcósmica</strong> — O ser humano é um microcosmo que reflete o macrocosmo</li>
<li><strong>Evolução espiritual</strong> — A alma humana está em um processo evolutivo através de múltiplas encarnações</li>
<li><strong>Sabedoria universal</strong> — Existe uma sabedoria primordial, acessível a todos os que buscam sinceramente, que está na base de todas as religiões</li>
<li><strong>Trabalho interior</strong> — O conhecimento teórico deve ser complementado pela transformação prática do ser</li>
</ol>

<div class="destaque-box">
<p><strong>Da teoria à prática:</strong> Tanto o Hermetismo quanto a Teosofia oferecem não apenas um corpo de conhecimento, mas um caminho de transformação. O estudo dos princípios herméticos e das doutrinas teosóficas deve ser acompanhado do trabalho interior — meditação, auto-observação e serviço ao próximo.</p>
</div>

<h2>Para Aprofundar</h2>
<p>Recomendações de estudo:</p>
<ul>
<li><em>Corpus Hermeticum</em> — Os textos originais do Hermetismo</li>
<li><em>O Caibalion</em> — Exposição moderna dos princípios herméticos</li>
<li><em>A Doutrina Secreta</em>, de H. P. Blavatsky</li>
<li><em>A Voz do Silêncio</em>, de H. P. Blavatsky</li>
<li>Estudos comparativos sobre a Filosofia Perene</li>
</ul>
HTML;
        $tags = 'hermetismo,teosofia,hermes trismegisto,blavatsky,princípios herméticos,caibalion,doutrina secreta,sabedoria universal';
        $fonte = 'Corpus Hermeticum; O Caibalion (Três Iniciados); H. P. Blavatsky, "A Doutrina Secreta"; Frances Yates, "Giordano Bruno e a Tradição Hermética"';
        inserirArtigo($pdo, $cid, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug;
    }
}

if (isset($catMap['filosofia-consciencia'])) {
    $cid = $catMap['filosofia-consciencia'];

    $slug = 'filosofia-perene-a-sabedoria-universal';
    if (!existeSlug($pdo, $slug)) {
        $titulo = 'Filosofia Perene: A Sabedoria Universal';
        $resumo = 'A Filosofia Perene é o reconhecimento de que existe um núcleo comum de verdades universais no coração de todas as grandes tradições espirituais. Uma exploração das ideias de Leibniz, Huxley e dos sábios de todos os tempos.';
        $conteudo = <<<'HTML'
<div class="destaque-box">
<p>A <strong>Filosofia Perene</strong> (<em>Philosophia Perennis</em>) é a ideia de que existe um corpo de verdades universais que se encontra no coração de todas as grandes tradições religiosas e filosóficas da humanidade. Estas verdades não são propriedade de nenhuma cultura ou época — são o patrimônio comum de toda a humanidade, redescoberto continuamente por aqueles que buscam a sabedoria.</p>
</div>

<h2>Origens do Conceito</h2>
<p>O termo "Filosofia Perene" foi cunhado por <strong>Gottfried Wilhelm Leibniz</strong> (1646-1716), o grande filósofo e matemático alemão. Leibniz acreditava que, abaixo da superfície das diferentes tradições religiosas e filosóficas, existia um sistema comum de verdades racionais e espirituais que podia ser demonstrado e compartilhado.</p>
<p>No século XX, o escritor <strong>Aldous Huxley</strong> popularizou o conceito em sua obra <em>A Filosofia Perene</em> (1945), uma antologia comentada de textos sagrados e filosóficos de todo o mundo, demonstrando a notável concordância entre tradições aparentemente díspares.</p>

<blockquote>"A Filosofia Perene é o sistema de verdades que, embora expressas em diferentes linguagens e imagens, constituem o fundamento comum de todas as grandes tradições espirituais da humanidade." — Aldous Huxley</blockquote>

<h2>As Verdades Universais</h2>
<p>Segundo Huxley e outros expoentes da Filosofia Perene, as verdades universais incluem:</p>

<ol>
<li><strong>Unidade do Ser</strong> — Existe uma Realidade Una, absoluta e transcendente, que é a fonte de toda existência. Esta Realidade é chamada de Brahman, Tao, Ain Soph, Deus, Pleroma — diferentes nomes para a mesma verdade.</li>
<li><strong>A Centelha Divina</strong> — O ser humano não é apenas um corpo e uma mente, mas possui uma centelha divina (Pneuma, Atman, Essência) que é da mesma natureza dessa Realidade Una.</li>
<li><strong>Autoconhecimento como Caminho</strong> — Conhecer a si mesmo é o caminho para conhecer a Realidade Una. "Conhece-te a ti mesmo" não é apenas um preceito moral, mas uma via de realização espiritual.</li>
<li><strong>Transformação Interior</strong> — O conhecimento não é suficiente; é preciso transformar-se — dissolver o ego, purificar o coração, desenvolver virtudes.</li>
<li><strong>Unidade da Humanidade</strong> — Todas as pessoas, independentemente de raça, cultura ou crença, compartilham a mesma natureza essencial e estão destinadas à mesma realização.</li>
</ol>

<h2>Tradições que Expressam a Filosofia Perene</h2>
<p>A Filosofia Perene não é uma tradição separada, mas o núcleo comum de múltiplas tradições:</p>

<h3>Platonismo e Neoplatonismo</h3>
<p>Platão (428-348 a.C.), com sua teoria das Formas (Ideias eternas e perfeitas), estabeleceu as bases da Filosofia Perene no Ocidente. Plotino (204-270 d.C.) desenvolveu estas ideias em um sistema completo, descrevendo a emanação do Uno, a queda da alma no mundo material e seu retorno através da contemplação.</p>

<h3>Gnose e Hermetismo</h3>
<p>O Gnosticismo e o Hermetismo preservaram e desenvolveram a Filosofia Perene no contexto do mundo helenístico e romano. Ambos enfatizam o conhecimento direto do Divino, a correspondência entre macrocosmo e microcosmo, e a necessidade da transformação interior.</p>

<h3>Teosofia</h3>
<p>A Teosofia moderna, fundada por Blavatsky, tentou sintetizar as expressões orientais e ocidentais da Filosofia Perene em um sistema coerente, baseado no estudo comparativo das religiões e na investigação das leis ocultas da natureza.</p>

<h3>Taoísmo e Budismo</h3>
<p>No Oriente, o Taoísmo (com seu conceito do Tao inefável) e o Budismo (com sua ênfase no despertar e na transcendência do ego) expressam as mesmas verdades universais em linguagens diferentes.</p>

<div class="destaque-box">
<p><strong>A unidade na diversidade:</strong> A Filosofia Perene não propõe que todas as religiões sejam iguais — cada uma tem suas particularidades históricas, culturais e doutrinárias. O que ela afirma é que, no nível mais profundo, todas apontam para a mesma realidade e oferecem caminhos que, embora diferentes em forma, convergem na essência.</p>
</div>

<h2>O Fio de Ouro</h2>
<p>Uma imagem poderosa utilizada pelos defensores da Filosofia Perene é a do <strong>"fio de ouro"</strong> que atravessa todas as tradições. Cada tradição é como uma pérola — única, bela, valiosa. Mas o que as une é o fio que as atravessa: as verdades universais do autoconhecimento, da transformação interior e da realização do Ser.</p>
<p>Este fio de ouro pode ser identificado no:</p>
<ul>
<li>Oráculo de Delfos: "Conhece-te a ti mesmo"</li>
<li>Os Upanishads hindus: "Tat tvam asi" (Isso és tu)</li>
<li>O Evangelho de Tomé: "O Reino está dentro de vós"</li>
<li>O Tao Te Ching: "O Tao que pode ser nomeado não é o Tao eterno"</li>
<li>O Sufismo: "Quem se conhece, conhece seu Senhor"</li>
</ul>

<h2>O Estudo da Filosofia Perene Hoje</h2>
<p>Para quem deseja se aprofundar na Filosofia Perene, recomenda-se:</p>
<ul>
<li>Leitura comparativa de textos sagrados de diferentes tradições</li>
<li>Estudo das obras de Aldous Huxley, Huston Smith e Frithjof Schuon</li>
<li>Prática espiritual em uma tradição viva, combinada com o estudo aberto de outras tradições</li>
<li>Participação em grupos de estudo inter-religiosos</li>
</ul>
HTML;
        $tags = 'filosofia perene,aldous huxley,platão,leibniz,gnose,hermetismo,teosofia,taoísmo,tradições espirituais';
        $fonte = 'Aldous Huxley, "A Filosofia Perene"; Platão, "República" e "Fédon"; Plotino, "Enéadas"; Frithjof Schuon, "A Unidade Transcendente das Religiões"';
        inserirArtigo($pdo, $cid, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug;
    }
}

if (isset($catMap['musica-sons'])) {
    $cid = $catMap['musica-sons'];

    $slug = 'musica-sagrada-e-frequencias-de-cura';
    if (!existeSlug($pdo, $slug)) {
        $titulo = 'Música Sagrada e Frequências de Cura';
        $resumo = 'O som e a música têm sido utilizados por todas as culturas como ferramentas de cura e elevação espiritual. Das frequências solfeggio aos mantras sagrados, descubra o poder transformador da vibração sonora.';
        $conteudo = <<<'HTML'
<div class="destaque-box">
<p>O som é a primeira manifestação da criação — "No princípio era o Verbo" (Logos). Todas as tradições espirituais reconhecem o poder do som como ferramenta de cura, transformação e conexão com o Divino. Dos mantras hindus aos cantos gregorianos, das frequências solfeggio à música clássica, o som nos oferece um caminho direto para estados superiores de consciência.</p>
</div>

<h2>O Poder do Som</h2>
<p>A física moderna confirma que toda matéria é vibração. Átomos, moléculas, células — tudo vibra em diferentes frequências. O som é uma onda que se propaga através de um meio (ar, água, tecidos) e que pode alterar a vibração deste meio. Quando ouvimos música ou entoamos um mantra, estamos literalmente <strong>re-sintonizando</strong> nosso corpo e nossa mente em novas frequências.</p>
<p>A <strong>cimática</strong> (do grego "kyma" = onda), ciência que estuda a visualização do som, demonstrou que frequências sonoras específicas produzem padrões geométricos na matéria — desde formas simples a estruturas complexas de incrível beleza e simetria. O som molda a realidade física.</p>

<h2>Mantras: O Som como Veículo Espiritual</h2>
<p>O termo sânscrito "mantra" deriva de "manas" (mente) e "trai" (proteger, liberar). Um mantra é um som ou sílaba sagrada que, quando repetida com concentração, protege a mente de suas distrações e a liberta de seus condicionamentos.</p>

<h3>OM (Aum) — O Mantra Primordial</h3>
<p>O mantra <strong>OM</strong> é considerado o som primordial do universo, a vibração da qual toda a criação emana. Representado pela sílaba sagrada A-U-M, OM simboliza os três estados da consciência (vigília, sonho e sono profundo) e o estado transcendente (Turiya). A vibração de OM ressoa na frequência de 432 Hz — a mesma frequência da ressonância natural do universo.</p>
<p>Praticar a entoação de OM diariamente (3-11 vezes) acalma a mente, equilibra o sistema nervoso e conecta o praticante com a vibração fundamental da realidade.</p>

<h3>Mantras Bija (Sementes)</h3>
<p>Os mantras <strong>bija</strong> são sementes sonoras que ativam chakras específicos. Cada chakra possui seu bija mantra:</p>
<ul>
<li><strong>Lam (Raiz)</strong> — ativa a segurança e o enraizamento</li>
<li><strong>Vam (Sacral)</strong> — desperta a criatividade e o prazer</li>
<li><strong>Ram (Plexo Solar)</strong> — fortalece a vontade e o poder pessoal</li>
<li><strong>Yam (Coração)</strong> — abre o amor e a compaixão</li>
<li><strong>Ham (Garganta)</strong> — clareza de expressão e verdade</li>
<li><strong>Ksham (Terceiro Olho)</strong> — intuição e percepção sutil</li>
<li><strong>OM (Coroa)</strong> — conexão com o Divino</li>
</ul>

<h2>Cantos Gregorianos e Música Sacra</h2>
<p>Os <strong>cantos gregorianos</strong>, desenvolvidos na Europa medieval, são uma das mais poderosas formas de música sagrada do Ocidente. Sua música monofônica, sem acompanhamento instrumental, cria um ambiente de profunda paz e recolhimento. Estudos demonstram que os cantos gregorianos induzem ondas cerebrais Alpha e Teta, facilitando estados meditativos profundos.</p>
<p>A <strong>música clássica</strong> de compositores como Bach, Mozart e Beethoven também possui propriedades terapêuticas. O chamado "Efeito Mozart" sugere que a música de Mozart, com suas estruturas matemáticas complexas e harmônicas, pode melhorar a cognição e equilibrar o sistema nervoso.</p>

<h2>Frequências Solfeggio</h2>
<p>As <strong>frequências solfeggio</strong> são uma escala de seis tons utilizada na música sacra antiga, redescoberta pelo Dr. Joseph Puleo na década de 1970. Cada frequência está associada a benefícios específicos:</p>

<ul>
<li><strong>396 Hz</strong> — Libertação do medo e da culpa</li>
<li><strong>417 Hz</strong> — Dissolução de traumas e mudanças positivas</li>
<li><strong>528 Hz</strong> — Reparação do DNA, transformação, milagres ("frequência do amor")</li>
<li><strong>639 Hz</strong> — Conexão e relacionamentos harmoniosos</li>
<li><strong>741 Hz</strong> — Expressão, soluções, limpeza de toxinas</li>
<li><strong>852 Hz</strong> — Despertar espiritual, retorno à ordem espiritual</li>
</ul>

<div class="destaque-box">
<p><strong>Prática diária com sons:</strong> Dedique 5-10 minutos por dia para ouvir ou entoar frequências específicas. Pela manhã, 528 Hz para iniciar o dia com amor e clareza. À noite, 396 Hz para liberar tensões do dia. Experimente combinar a audição com respiração consciente para potencializar os efeitos.</p>
</div>

<h2>Cimática: A Visualização do Som</h2>
<p>A <strong>cimática</strong>, desenvolvida pelo médico suíço Hans Jenny na década de 1960, demonstra que frequências sonoras específicas criam padrões geométricos na matéria. Quando uma placa coberta com pó ou líquido é vibrada em diferentes frequências, formam-se figuras geométricas que lembram mandalas, flores, estrelas e outras formas naturais.</p>
<p>Esta ciência nos mostra que o som não é apenas audível — ele é criativo, estruturante. Cada frequência produz uma forma, cada forma corresponde a uma frequência. A realidade material é, em última análise, som solidificado.</p>
HTML;
        $tags = 'música sagrada,frequências solfeggio,mantras,om,cantos gregorianos,cimática,cura pelo som,frequências de cura';
        $fonte = 'Hans Jenny, "Cymatics"; Dr. Joseph Puleo, "Frequências Solfeggio"; Jonathan Goldman, "Healing Sounds"; Tradições tântricas e yogues';
        inserirArtigo($pdo, $cid, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug;
    }
}

if (isset($catMap['frequencias-cura'])) {
    $cid = $catMap['frequencias-cura'];

    $slug = 'coerencia-cardiaca-e-campo-eletromagnetico';
    if (!existeSlug($pdo, $slug)) {
        $titulo = 'Coerência Cardíaca e Campo Eletromagnético';
        $resumo = 'O coração é muito mais que uma bomba mecânica: possui 40 mil neurônios e gera o campo eletromagnético mais poderoso do corpo. Descubra como a coerência cardíaca transforma a saúde e a consciência.';
        $conteudo = <<<'HTML'
<div class="destaque-box">
<p>O coração sempre foi símbolo universal do amor, da intuição e da conexão espiritual. Hoje, a ciência está confirmando que este símbolo ancestral corresponde a uma realidade fisiológica extraordinária: o coração possui seu próprio "cérebro" e gera um campo eletromagnético que se estende por metros ao redor do corpo — um órgão não apenas de circulação, mas de <strong>percepção, comunicação e coerência</strong>.</p>
</div>

<h2>O Cérebro do Coração</h2>
<p>Pesquisas do <strong>HeartMath Institute</strong> (Instituto de Matemática do Coração) revelaram que o coração possui um sistema nervoso intrínseco complexo, composto por aproximadamente <strong>40.000 neurônios</strong> — os mesmos tipos de células encontradas no cérebro. Este "coração cerebral" processa informações, toma decisões e se comunica bidirecionalmente com o cérebro craniano.</p>
<p>O coração envia mais informações para o cérebro do que recebe dele. Através de vias neurais, hormonais e eletromagnéticas, o coração influencia:</p>
<ul>
<li>A percepção emocional</li>
<li>A tomada de decisões</li>
<li>A função cognitiva</li>
<li>O sistema imunológico</li>
<li>O equilíbrio hormonal</li>
</ul>

<blockquote>"O coração não é apenas um órgão mecânico, mas um centro de percepção e inteligência que desempenha um papel fundamental na experiência da consciência." — Dr. Rollin McCraty, HeartMath Institute</blockquote>

<h2>O Campo Eletromagnético do Coração</h2>
<p>O coração gera o campo eletromagnético mais poderoso do corpo humano — aproximadamente <strong>5.000 vezes mais forte</strong> que o campo gerado pelo cérebro. Este campo se estende por cerca de <strong>3 metros</strong> ao redor do corpo em todas as direções.</p>
<p>Características do campo cardíaco:</p>
<ul>
<li><strong>Forma toroidal</strong> — como um donut tridimensional, com energia fluindo do centro para fora e retornando</li>
<li><strong>Varredura contínua</strong> — o coração gera ondas eletromagnéticas rítmicas que se propagam pelo ambiente</li>
<li><strong>Transporte de informação</strong> — o campo cardíaco carrega informações sobre nosso estado emocional e fisiológico</li>
<li><strong>Interação interpessoal</strong> — os campos cardíacos de pessoas próximas interagem entre si, influenciando-se mutuamente</li>
</ul>
<p>Esta descoberta tem implicações profundas: não nos comunicamos apenas através de palavras e gestos, mas também através de nossos campos cardíacos. Quando estamos em um estado de coerência cardíaca, nosso campo se torna mais coerente e harmonioso, influenciando positivamente as pessoas ao nosso redor.</p>

<h2>Coerência Cardíaca</h2>
<p>A <strong>coerência cardíaca</strong> é um estado fisiológico no qual o coração, o cérebro e o sistema nervoso entram em sincronia rítmica. Neste estado, a variabilidade da frequência cardíaca (VFC) — a variação natural no intervalo entre batimentos cardíacos — se torna altamente ordenada, formando um padrão de onda senoidal suave e regular.</p>

<h3>Benefícios da Coerência Cardíaca</h3>
<ul>
<li>Redução do estresse e da ansiedade</li>
<li>Melhora da função imunológica</li>
<li>Equilíbrio do sistema nervoso autônomo</li>
<li>Aumento da clareza mental e da intuição</li>
<li>Melhora da qualidade do sono</li>
<li>Redução da pressão arterial</li>
<li>Maior sensação de bem-estar e conexão</li>
<li>Melhora do desempenho cognitivo</li>
</ul>

<h2>A Pesquisa HeartMath</h2>
<p>O <strong>HeartMath Institute</strong>, fundado em 1991 nos EUA, tem sido a principal instituição de pesquisa sobre coerência cardíaca. Seus estudos demonstraram que é possível aprender a entrar em estado de coerência cardíaca através de técnicas simples, e que este estado produz mudanças fisiológicas mensuráveis em minutos.</p>
<p>Uma descoberta particularmente fascinante é que o <strong>coração pode "sentir" antes do cérebro processar</strong> — o chamado "coração intuitivo". Experimentos mostraram que o coração responde a estímulos emocionais alguns segundos antes do cérebro conscientemente percebê-los, sugerindo que o coração possui uma forma de percepção pré-cognitiva.</p>

<h3>Prática Diária de Coerência Cardíaca</h3>
<ol>
<li><strong>Respiração coerente</strong> — 5 minutos, 3 vezes ao dia. Inspire por 5 segundos, expire por 5 segundos. Mantenha o ritmo constante.</li>
<li><strong>Foco no coração</strong> — Direcione sua atenção para a região do coração. Sinta ou imagine a respiração entrando e saindo pelo centro do peito.</li>
<li><strong>Emoção positiva</strong> — Evocar uma emoção genuína de apreciação, gratidão ou amor. O coração responde à qualidade emocional.</li>
<li><strong>Sentir a expansão</strong> — Sinta o campo do coração se expandindo, preenchendo todo o corpo e se estendendo ao ambiente.</li>
</ol>

<div class="destaque-box">
<p><strong>Coerência é prática:</strong> A coerência cardíaca não é um estado que se alcança uma vez e se mantém para sempre. É uma habilidade que se desenvolve com a prática regular. Quanto mais você pratica, mais rapidamente consegue acessar o estado de coerência — inclusive em situações desafiadoras.</p>
</div>

<h2>O Coração e a Consciência</h2>
<p>As descobertas do HeartMath se alinham perfeitamente com os ensinamentos das tradições espirituais. O coração como centro da consciência, como sede do Ser, como órgão de percepção sutil — estas ideias são milenares. A ciência está apenas começando a mapear um território que os sábios sempre conheceram: o coração é a porta de entrada para dimensões mais profundas da realidade.</p>
<p>Nas práticas de meditação gnóstica e tântrica, o coração (Anahata chakra) é considerado o centro da consciência desperta. Trabalhar com o coração — não apenas como símbolo, mas como órgão vivo de percepção — é fundamental para o despertar da consciência e a conexão com o Divino.</p>
HTML;
        $tags = 'coerência cardíaca,heartmath,campo eletromagnético,neurônios cardíacos,variabilidade cardíaca,meditação do coração,ciência da consciência';
        $fonte = 'HeartMath Institute; Rollin McCraty, "The Coherent Heart"; Paul Pearsall, "The Heart\'s Code"; Stephen Porges, "Teoria Polivagal"';
        inserirArtigo($pdo, $cid, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug;
    }
}

if (isset($catMap['historia-cultura'])) {
    $cid = $catMap['historia-cultura'];

    $slug = 'civilizacoes-perdidas-e-sabedoria-antiga';
    if (!existeSlug($pdo, $slug)) {
        $titulo = 'Civilizações Perdidas e Sabedoria Antiga';
        $resumo = 'Atlântida, Egito, Lemúria — as tradições de todo o mundo falam de civilizações antigas que possuíam um conhecimento espiritual profundo. Existiria um fio de ouro conectando estas culturas?';
        $conteudo = <<<'HTML'
<div class="destaque-box">
<p>A história da humanidade, segundo as tradições de sabedoria, é muito mais antiga e rica do que os livros de história nos contam. Civilizações avançadas — tanto material quanto espiritualmente — teriam existido em um passado remoto, deixando como legado não apenas monumentos de pedra, mas um corpo de conhecimento espiritual que sobrevive até hoje através das tradições iniciáticas.</p>
</div>

<h2>Atlântida: O Continente Perdido</h2>
<p>A primeira menção de <strong>Atlântida</strong> vem de Platão, em seus diálogos <em>Timeu</em> e <em>Crítias</em> (c. 360 a.C.). Segundo Platão, Atlântida era uma grande ilha-continente além das Colunas de Hércules (Estreito de Gibraltar), que abrigava uma civilização poderosa e avançada. Por sua arrogância e corrupção moral, os atlantes teriam sido punidos pelos deuses, e sua ilha submergiu no oceano em "um único dia e uma noite terríveis".</p>
<p>Para os teósofos e esoteristas, Atlântida não é apenas um mito, mas um período histórico real — a quarta Raça Raiz da humanidade, segundo a cosmologia teosófica. Os atlantes possuíam grandes poderes psíquicos e um conhecimento avançado das leis da natureza, mas seu abuso destes poderes levou à sua queda.</p>
<p>Seja como lenda ou como história real, Atlântida simboliza a sabedoria perdida que a humanidade precisa redescobrir — e o perigo do conhecimento sem sabedoria.</p>

<blockquote>"Atlântida não é apenas uma ilha submersa; é um estado de consciência que precisa ser resgatado das profundezas do esquecimento." — Tradição esotérica</blockquote>

<h2>Egito: As Escolas de Mistérios</h2>
<p>O <strong>Egito Antigo</strong> é reconhecido por todas as tradições esotéricas como um dos grandes centros de sabedoria da humanidade. Os "Mistérios Egípcios" — rituais de iniciação realizados nos templos de Ísis, Osíris e Hórus — foram a base da qual se alimentaram os filósofos gregos (Pitágoras, Platão, Plotino) e as tradições herméticas e gnósticas posteriores.</p>
<p>Características da sabedoria egípcia:</p>
<ul>
<li><strong>Conhecimento astronômico e geométrico</strong> — as pirâmides e templos eram alinhados com precisão celestial</li>
<li><strong>Iniciação gradual</strong> — o candidato passava por provas e ensinamentos progressivos nos mistérios</li>
<li><strong>Livro dos Mortos</strong> — guia para a alma na jornada pós-morte, na verdade um mapa da psicologia humana</li>
<li><strong>Símbolos e hieróglifos</strong> — linguagem sagrada que codificava múltiplos níveis de significado</li>
<li><strong>O deus Thoth</strong> — identificado com Hermes Trismegisto, patrono da sabedoria, da escrita e da magia</li>
</ul>

<h2>Lemúria: O Continente Mãe</h2>
<p>A <strong>Lemúria</strong> (ou Mu) é outro continente perdido mencionado nas tradições esotéricas. Segundo a Teosofia, Lemúria teria existido no Oceano Pacífico e abrigado a terceira Raça Raiz da humanidade. Os lemurianos eram gigantes, de natureza mais espiritual que física, e viviam em harmonia com a natureza.</p>
<p>Apesar de não haver evidências geológicas, a Lemúria permanece como um símbolo poderoso: a inocência primordial da humanidade, o estado paradisíaco antes da queda na matéria densa.</p>

<h2>Grandes Tradições e o Fio de Ouro</h2>
<p>Ao redor do mundo, diversas culturas antigas preservaram fragmentos da sabedoria primordial:</p>

<h3>Índia</h3>
<p>Os Vedas, Upanishads e a literatura tântrica indiana constituem um dos maiores tesouros de sabedoria espiritual da humanidade. O conceito de <strong>Dharma</strong> (lei cósmica), <strong>Karma</strong> (causa e efeito) e <strong>Moksha</strong> (libertação) são ensinamentos universais que transcendem a cultura indiana.</p>

<h3>Tibet</h3>
<p>O Budismo Tibetano preservou não apenas os ensinamentos de Buda, mas também tradições xamânicas e tântricas pré-budistas (Bön). Técnicas avançadas de meditação, visualização e trabalho energético são ensinadas em mosteiros que guardam a sabedoria há milênios.</p>

<h3>Mesoamérica</h3>
<p>Os maias, astecas e incas possuíam calendários astronômicos de precisão impressionante, conhecimentos matemáticos avançados e uma cosmologia que descrevia ciclos de criação e destruição cósmicos. O <em>Popol Vuh</em> maia é um texto de extraordinária profundidade espiritual.</p>

<h3>Celtas</h3>
<p>Os druidas celtas eram os guardiões de uma tradição de sabedoria baseada na natureza, nos ciclos das estações e na conexão com o mundo invisível. Sua compreensão da alma, da reencarnação e do mundo espiritual tem paralelos profundos com as tradições orientais.</p>

<div class="destaque-box">
<p><strong>O fio de ouro:</strong> Todas estas tradições, separadas por oceanos e milênios, compartilham ensinamentos notavelmente semelhantes. A existência de uma alma imortal, a lei de causa e efeito, a possibilidade de despertar espiritual, a importância do autoconhecimento — estes ensinamentos aparecem em todas elas, sugerindo uma origem comum: a Filosofia Perene da humanidade.</p>
</div>

<h2>Lições para o Buscador</h2>
<p>O estudo das civilizações antigas não é mera curiosidade histórica. Ele nos convida a:</p>
<ul>
<li><strong>Humildade</strong> — reconhecer que nossos antepassados possuíam conhecimentos que ainda não redescobrimos</li>
<li><strong>Pesquisa</strong> — buscar a sabedoria onde ela estiver, sem preconceitos acadêmicos ou dogmas</li>
<li><strong>Prática</strong> — não apenas estudar, mas viver os ensinamentos, aplicando-os no trabalho interior diário</li>
<li><strong>Conexão</strong> — sentir-se parte de uma corrente de buscadores que atravessa os milênios</li>
</ul>
HTML;
        $tags = 'atlântida,egito antigo,lemúria,mistérios egípcios,filosofia perene,civilizações perdidas,sabedoria antiga,teosofia';
        $fonte = 'Platão, "Timeu" e "Crítias"; H. P. Blavatsky, "A Doutrina Secreta"; Lewis Spence, "Atlantis"; Graham Hancock, "Pegadas dos Deuses"; Joseph Campbell, "O Herói de Mil Faces"';
        inserirArtigo($pdo, $cid, $titulo, $slug, $resumo, $conteudo, $tags, $fonte, $agora);
        $criados++;
    } else {
        $ignorados[] = $slug;
    }
}

$emBranco = $db->select("
    SELECT c.id, c.slug, c.nome,
           (SELECT COUNT(*) FROM artigos WHERE categoria_id = c.id) as qtd
    FROM categorias c
    HAVING qtd = 0
    ORDER BY c.nome ASC
");

echo "=== RESUMO DA OPERACAO ===\n";
echo "Artigos criados: {$criados}\n";
if (!empty($ignorados)) {
    echo "Artigos ignorados (slug ja existia): " . implode(', ', $ignorados) . "\n";
}
if (!empty($emBranco)) {
    echo "\nCategorias ainda vazias (" . count($emBranco) . "):\n";
    foreach ($emBranco as $cat) {
        echo "  - {$cat['slug']} ({$cat['nome']})\n";
    }
} else {
    echo "\nTodas as categorias agora possuem artigos!\n";
}
echo "=== FIM ===\n";
