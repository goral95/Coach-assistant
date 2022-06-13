package pl.lukaszgorczyk.coachassistant.controller;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import pl.lukaszgorczyk.coachassistant.model.Player;
import pl.lukaszgorczyk.coachassistant.model.PlayerRepository;

import javax.validation.Valid;
import java.net.URI;
import java.util.List;


@RestController
@RequestMapping("/players")
class PlayerController {
    public static final Logger logger = LoggerFactory.getLogger(PlayerController.class);
    private final PlayerRepository repository;

    public PlayerController(PlayerRepository repository) {
        this.repository = repository;
    }

    @GetMapping(params = {"!sort", "!page", "!size"} )
    ResponseEntity<List<Player>> readAllPlayers() {
        logger.warn("Exposing all players");
        return ResponseEntity.ok(repository.findAll());
    }

    @GetMapping("/{id}")
    ResponseEntity<Player> findPlayer(@PathVariable int id){
        logger.warn("Exposing player of id:" + id);
        return  repository.findById(id)
                .map(ResponseEntity::ok)
                .orElse(ResponseEntity.notFound().build());
    }

    @PostMapping
    ResponseEntity<Player> addTask(@RequestBody @Valid Player toCreate){
        logger.warn("Add player");
        Player result = repository.save(toCreate);
        return ResponseEntity.created(URI.create("/" + result.getId())).body(result);
    }
}
